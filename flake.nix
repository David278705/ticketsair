{
  description = "Dev shell: Laravel (PHP 8.3) + Vue + MySQL (Docker) - NixOS 25.05";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-25.05";
    flake-utils.url = "github:numtide/flake-utils";
  };

  outputs = { self, nixpkgs, flake-utils }:
    flake-utils.lib.eachDefaultSystem (system:
      let
        pkgs = import nixpkgs { inherit system; };

        # PHP 8.3 con extensiones (forma correcta en 25.05)
        php = pkgs.php83.buildEnv {
          extensions = ({ enabled, all }:
            enabled ++ (with all; [
              bcmath
              curl
              dom
              fileinfo
              filter
              gd
              intl
              mbstring
              opcache
              openssl
              pdo_mysql
              sockets
              sodium
              tokenizer
              xml
              xmlreader
              xmlwriter
              zip
            ]));
          # extraConfig = "memory_limit = 1G"; # si quieres tunear php.ini
        };

        # Composer: existe como phpPackages.composer (o php83Packages.composer)
        composerPkg =
          if pkgs ? phpPackages && pkgs.phpPackages ? composer then pkgs.phpPackages.composer
          else if pkgs ? php83Packages && pkgs.php83Packages ? composer then pkgs.php83Packages.composer
          else pkgs.writeShellScriptBin "composer" ''
            echo "Composer no encontrado en este pin de nixpkgs." >&2
            exit 1
          '';
      in
      {
        devShells.default = pkgs.mkShell {
          packages = [
            php
            composerPkg
            pkgs.nodejs_22
            pkgs.mysql-client
            pkgs.git
            pkgs.pkg-config
            pkgs.openssl
            pkgs.libzip
            pkgs.icu
          ];

          shellHook = ''
            echo "🟢 Laravel + Vue listo (PHP $(php -r 'echo PHP_VERSION;'))"
            export PATH="$PWD/vendor/bin:$PATH"
          '';
        };
      });
}

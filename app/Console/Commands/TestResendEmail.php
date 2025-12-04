<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\PurchaseMail;
use App\Mail\ReservationConfirmationMail;
use App\Mail\BoardingPassMail;
use App\Models\User;
use App\Models\Booking;

class TestResendEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test-resend {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía un correo de prueba usando Resend';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('🚀 Probando configuración de Resend...');
        $this->newLine();

        // Verificar configuración
        if (config('mail.default') !== 'resend') {
            $this->error('❌ MAIL_MAILER no está configurado como "resend"');
            $this->info('   Actual: ' . config('mail.default'));
            return 1;
        }

        if (!config('services.resend.key')) {
            $this->error('❌ RESEND_KEY no está configurada');
            return 1;
        }

        $this->info('✅ Configuración correcta');
        $this->info('   Mailer: ' . config('mail.default'));
        $this->info('   From: ' . config('mail.from.address'));
        $this->newLine();

        // Enviar correo de prueba simple
        $this->info('📧 Enviando correo de prueba a: ' . $email);
        
        try {
            Mail::raw('¡Hola! Este es un correo de prueba desde TicketsAir usando Resend. Si recibes esto, la configuración está correcta. ✅', function ($message) use ($email) {
                $message->to($email)
                    ->subject('🧪 Prueba de Resend - TicketsAir');
            });

            $this->info('✅ Correo enviado exitosamente');
            $this->newLine();
            $this->info('📊 Verifica tu inbox y el dashboard de Resend:');
            $this->info('   https://resend.com/emails');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error al enviar correo:');
            $this->error('   ' . $e->getMessage());
            $this->newLine();
            $this->info('💡 Verifica:');
            $this->info('   1. Que RESEND_KEY sea válida');
            $this->info('   2. Que el dominio en MAIL_FROM_ADDRESS esté verificado');
            $this->info('   3. Los logs en storage/logs/laravel.log');
            
            return 1;
        }
    }
}

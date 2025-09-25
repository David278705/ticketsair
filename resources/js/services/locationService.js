import axios from 'axios'

class LocationService {
    constructor() {
        // Countries Now API - URL corregida
        this.countriesNowAPI = 'https://countriesnow.space/api/v0.1/countries'
        
        // Headers estándar
        this.headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    }

    /**
     * Obtiene lista de países desde Countries Now API
     */
    async getCountries() {
        try {
            const response = await axios.get(this.countriesNowAPI, {
                headers: this.headers
            })
            
            if (response.data && response.data.error === false && response.data.data) {
                const countries = response.data.data
                    .map(country => ({
                        code: country.iso2,
                        name: country.country,
                        iso3: country.iso3
                    }))
                    .sort((a, b) => a.name.localeCompare(b.name))

                return countries
            } else {
                throw new Error('API response indicates error or empty data')
            }
        } catch (error) {
            console.warn('⚠️ Error obteniendo países desde API, usando fallback estático:', error.message)
            return this.getStaticCountries()
        }
    }

    /**
     * Obtiene estados/regiones de un país usando Countries Now API
     */
    async getStates(countryCode) {
        try {
            // Buscar el nombre del país en los datos ya cargados
            const countryName = await this.getCountryNameByCode(countryCode)
            
            if (countryName) {
                const response = await axios.post(`${this.countriesNowAPI}/states`, 
                    { country: countryName }, 
                    { headers: this.headers }
                )
                
                if (response.data && response.data.error === false && response.data.data && response.data.data.states) {
                    const states = response.data.data.states.map((state, index) => ({
                        id: `${countryCode}_${index}`,
                        name: this.cleanStateName(state.name),
                        originalName: state.name, // Guardamos el nombre original para las peticiones
                        code: state.state_code || state.name.substring(0, 2).toUpperCase()
                    }))
                    
                    if (states.length > 0) {
                        return states
                    }
                } else {
                    console.warn(`⚠️ No se encontraron estados para ${countryName} en la API`)
                }
            } else {
                console.warn(`⚠️ No se encontró nombre para el código de país: ${countryCode}`)
            }
            return this.getStaticStates(countryCode)
        } catch (error) {
            console.error('Error fetching states:', error)
            // Retornar estados estáticos como fallback
            return this.getStaticStates(countryCode)
        }
    }

    /**
     * Obtiene ciudades de un estado/región usando Countries Now API
     */
    async getCities(countryName, stateName) {
        try {
            if (countryName && stateName) {
                const response = await axios.post(`${this.countriesNowAPI}/state/cities`, 
                    { 
                        country: countryName,
                        state: stateName
                    }, 
                    { headers: this.headers }
                )
                
                if (response.data && response.data.data) {
                    const cities = response.data.data.map((cityName, index) => ({
                        id: `${countryName}_${stateName}_${index}`,
                        name: cityName,
                        population: 0 // La API no devuelve población
                    }))
                    
                    if (cities.length > 0) {
                        return cities.slice(0, 50) // Aumentamos el límite a 50 ciudades
                    }
                }
            }
            
            // Fallback a datos estáticos
            return []
        } catch (error) {
            console.error('LocationService: Error fetching cities:', error)
            return []
        }
    }

    /**
     * Busca ciudades por nombre (para casos donde no se puede navegar jerárquicamente)
     */
    async searchCities(query, countryCode) {
        try {
            // Buscar en datos estáticos primero
            const staticResults = this.searchStaticCities(query, countryCode)
            if (staticResults.length > 0) {
                return staticResults
            }
            
            // Fallback a GeoNames
            const response = await axios.get(
                `${this.geonamesAPI}/searchJSON?q=${encodeURIComponent(query)}&country=${countryCode}&featureClass=P&maxRows=20&username=${this.geonamesUsername}`
            )
            
            if (response.data && response.data.geonames) {
                return response.data.geonames.map(city => ({
                    id: city.geonameId,
                    name: city.name,
                    adminName: city.adminName1,
                    population: city.population || 0
                }))
            }
            
            return []
        } catch (error) {
            console.error('Error searching cities:', error)
            return this.searchStaticCities(query, countryCode)
        }
    }

    /**
     * Buscar en ciudades estáticas
     */
    searchStaticCities(query, countryCode) {
        const states = this.getStaticStates(countryCode)
        const results = []
        
        states.forEach(state => {
            const cities = this.getStaticCities(countryCode, state.code)
            cities.forEach(city => {
                if (city.name.toLowerCase().includes(query.toLowerCase())) {
                    results.push({
                        id: city.id,
                        name: city.name,
                        adminName: state.name,
                        population: city.population
                    })
                }
            })
        })
        
        return results.sort((a, b) => b.population - a.population)
    }

    /**
     * Mapeo de códigos de país a GeoName IDs
     */
    getCountryGeonameId(countryCode) {
        const mapping = {
            'US': 6252001,  // Estados Unidos
            'CA': 6251999,  // Canadá
            'MX': 3996063,  // México
            'BR': 3469034,  // Brasil
            'AR': 3865483,  // Argentina
            'CO': 3686110,  // Colombia
            'PE': 3932488,  // Perú
            'CL': 3895114,  // Chile
            'ES': 2510769,  // España
            'FR': 3017382,  // Francia
            'DE': 2921044,  // Alemania
            'IT': 3175395,  // Italia
            'GB': 2635167,  // Reino Unido
            'CN': 1814991,  // China
            'JP': 1861060,  // Japón
            'IN': 1269750,  // India
            'AU': 2077456,  // Australia
            // Agregar más según necesidad
        }
        
        return mapping[countryCode] || null
    }

    /**
     * Países de fallback en caso de error
     */
    getFallbackCountries() {
        return [
            { code: 'US', name: 'Estados Unidos', iso3: 'USA' },
            { code: 'CA', name: 'Canadá', iso3: 'CAN' },
            { code: 'MX', name: 'México', iso3: 'MEX' },
            { code: 'BR', name: 'Brasil', iso3: 'BRA' },
            { code: 'AR', name: 'Argentina', iso3: 'ARG' },
            { code: 'CO', name: 'Colombia', iso3: 'COL' },
            { code: 'PE', name: 'Perú', iso3: 'PER' },
            { code: 'CL', name: 'Chile', iso3: 'CHL' },
            { code: 'ES', name: 'España', iso3: 'ESP' },
            { code: 'FR', name: 'Francia', iso3: 'FRA' },
            { code: 'DE', name: 'Alemania', iso3: 'DEU' },
            { code: 'IT', name: 'Italia', iso3: 'ITA' },
            { code: 'GB', name: 'Reino Unido', iso3: 'GBR' },
            { code: 'CN', name: 'China', iso3: 'CHN' },
            { code: 'JP', name: 'Japón', iso3: 'JPN' },
            { code: 'IN', name: 'India', iso3: 'IND' },
            { code: 'AU', name: 'Australia', iso3: 'AUS' }
        ]
    }

    /**
     * Obtiene el nombre del país por su código ISO
     * Primero intenta buscar en los datos cargados de la API, luego usa mapeo estático
     */
    async getCountryNameByCode(countryCode) {
        try {
            // Intentar obtener países de la API primero
            const countries = await this.getCountries()
            const country = countries.find(c => c.code === countryCode)
            if (country) {
                return country.name
            }
        } catch (error) {
            console.warn('Error obteniendo países para mapeo:', error.message)
        }

        // Fallback con mapeo estático más completo
        const countryMapping = {
            'AD': 'Andorra',
            'AE': 'United Arab Emirates',
            'AF': 'Afghanistan', 
            'AG': 'Antigua and Barbuda',
            'AI': 'Anguilla',
            'AL': 'Albania',
            'AM': 'Armenia',
            'AO': 'Angola',
            'AQ': 'Antarctica',
            'AR': 'Argentina',
            'AS': 'American Samoa',
            'AT': 'Austria',
            'AU': 'Australia',
            'AW': 'Aruba',
            'AX': 'Åland Islands',
            'AZ': 'Azerbaijan',
            'BA': 'Bosnia and Herzegovina',
            'BB': 'Barbados',
            'BD': 'Bangladesh',
            'BE': 'Belgium',
            'BF': 'Burkina Faso',
            'BG': 'Bulgaria',
            'BH': 'Bahrain',
            'BI': 'Burundi',
            'BJ': 'Benin',
            'BL': 'Saint Barthélemy',
            'BM': 'Bermuda',
            'BN': 'Brunei',
            'BO': 'Bolivia',
            'BQ': 'Caribbean Netherlands',
            'BR': 'Brazil',
            'BS': 'Bahamas',
            'BT': 'Bhutan',
            'BV': 'Bouvet Island',
            'BW': 'Botswana',
            'BY': 'Belarus',
            'BZ': 'Belize',
            'CA': 'Canada',
            'CC': 'Cocos Islands',
            'CD': 'DR Congo',
            'CF': 'Central African Republic',
            'CG': 'Republic of the Congo',
            'CH': 'Switzerland',
            'CI': 'Ivory Coast',
            'CK': 'Cook Islands',
            'CL': 'Chile',
            'CM': 'Cameroon',
            'CN': 'China',
            'CO': 'Colombia',
            'CR': 'Costa Rica',
            'CU': 'Cuba',
            'CV': 'Cape Verde',
            'CW': 'Curaçao',
            'CX': 'Christmas Island',
            'CY': 'Cyprus',
            'CZ': 'Czech Republic',
            'DE': 'Germany',
            'DJ': 'Djibouti',
            'DK': 'Denmark',
            'DM': 'Dominica',
            'DO': 'Dominican Republic',
            'DZ': 'Algeria',
            'EC': 'Ecuador',
            'EE': 'Estonia',
            'EG': 'Egypt',
            'EH': 'Western Sahara',
            'ER': 'Eritrea',
            'ES': 'Spain',
            'ET': 'Ethiopia',
            'FI': 'Finland',
            'FJ': 'Fiji',
            'FK': 'Falkland Islands',
            'FM': 'Micronesia',
            'FO': 'Faroe Islands',
            'FR': 'France',
            'GA': 'Gabon',
            'GB': 'United Kingdom',
            'GD': 'Grenada',
            'GE': 'Georgia',
            'GF': 'French Guiana',
            'GG': 'Guernsey',
            'GH': 'Ghana',
            'GI': 'Gibraltar',
            'GL': 'Greenland',
            'GM': 'Gambia',
            'GN': 'Guinea',
            'GP': 'Guadeloupe',
            'GQ': 'Equatorial Guinea',
            'GR': 'Greece',
            'GS': 'South Georgia',
            'GT': 'Guatemala',
            'GU': 'Guam',
            'GW': 'Guinea-Bissau',
            'GY': 'Guyana',
            'HK': 'Hong Kong',
            'HM': 'Heard Island and McDonald Islands',
            'HN': 'Honduras',
            'HR': 'Croatia',
            'HT': 'Haiti',
            'HU': 'Hungary',
            'ID': 'Indonesia',
            'IE': 'Ireland',
            'IL': 'Israel',
            'IM': 'Isle of Man',
            'IN': 'India',
            'IO': 'British Indian Ocean Territory',
            'IQ': 'Iraq',
            'IR': 'Iran',
            'IS': 'Iceland',
            'IT': 'Italy',
            'JE': 'Jersey',
            'JM': 'Jamaica',
            'JO': 'Jordan',
            'JP': 'Japan',
            'KE': 'Kenya',
            'KG': 'Kyrgyzstan',
            'KH': 'Cambodia',
            'KI': 'Kiribati',
            'KM': 'Comoros',
            'KN': 'Saint Kitts and Nevis',
            'KP': 'North Korea',
            'KR': 'South Korea',
            'KW': 'Kuwait',
            'KY': 'Cayman Islands',
            'KZ': 'Kazakhstan',
            'LA': 'Laos',
            'LB': 'Lebanon',
            'LC': 'Saint Lucia',
            'LI': 'Liechtenstein',
            'LK': 'Sri Lanka',
            'LR': 'Liberia',
            'LS': 'Lesotho',
            'LT': 'Lithuania',
            'LU': 'Luxembourg',
            'LV': 'Latvia',
            'LY': 'Libya',
            'MA': 'Morocco',
            'MC': 'Monaco',
            'MD': 'Moldova',
            'ME': 'Montenegro',
            'MF': 'Saint Martin',
            'MG': 'Madagascar',
            'MH': 'Marshall Islands',
            'MK': 'North Macedonia',
            'ML': 'Mali',
            'MM': 'Myanmar',
            'MN': 'Mongolia',
            'MO': 'Macao',
            'MP': 'Northern Mariana Islands',
            'MQ': 'Martinique',
            'MR': 'Mauritania',
            'MS': 'Montserrat',
            'MT': 'Malta',
            'MU': 'Mauritius',
            'MV': 'Maldives',
            'MW': 'Malawi',
            'MX': 'Mexico',
            'MY': 'Malaysia',
            'MZ': 'Mozambique',
            'NA': 'Namibia',
            'NC': 'New Caledonia',
            'NE': 'Niger',
            'NF': 'Norfolk Island',
            'NG': 'Nigeria',
            'NI': 'Nicaragua',
            'NL': 'Netherlands',
            'NO': 'Norway',
            'NP': 'Nepal',
            'NR': 'Nauru',
            'NU': 'Niue',
            'NZ': 'New Zealand',
            'OM': 'Oman',
            'PA': 'Panama',
            'PE': 'Peru',
            'PF': 'French Polynesia',
            'PG': 'Papua New Guinea',
            'PH': 'Philippines',
            'PK': 'Pakistan',
            'PL': 'Poland',
            'PM': 'Saint Pierre and Miquelon',
            'PN': 'Pitcairn Islands',
            'PR': 'Puerto Rico',
            'PS': 'Palestine',
            'PT': 'Portugal',
            'PW': 'Palau',
            'PY': 'Paraguay',
            'QA': 'Qatar',
            'RE': 'Réunion',
            'RO': 'Romania',
            'RS': 'Serbia',
            'RU': 'Russia',
            'RW': 'Rwanda',
            'SA': 'Saudi Arabia',
            'SB': 'Solomon Islands',
            'SC': 'Seychelles',
            'SD': 'Sudan',
            'SE': 'Sweden',
            'SG': 'Singapore',
            'SH': 'Saint Helena',
            'SI': 'Slovenia',
            'SJ': 'Svalbard and Jan Mayen',
            'SK': 'Slovakia',
            'SL': 'Sierra Leone',
            'SM': 'San Marino',
            'SN': 'Senegal',
            'SO': 'Somalia',
            'SR': 'Suriname',
            'SS': 'South Sudan',
            'ST': 'São Tomé and Príncipe',
            'SV': 'El Salvador',
            'SX': 'Sint Maarten',
            'SY': 'Syria',
            'SZ': 'Eswatini',
            'TC': 'Turks and Caicos Islands',
            'TD': 'Chad',
            'TF': 'French Southern and Antarctic Lands',
            'TG': 'Togo',
            'TH': 'Thailand',
            'TJ': 'Tajikistan',
            'TK': 'Tokelau',
            'TL': 'Timor-Leste',
            'TM': 'Turkmenistan',
            'TN': 'Tunisia',
            'TO': 'Tonga',
            'TR': 'Turkey',
            'TT': 'Trinidad and Tobago',
            'TV': 'Tuvalu',
            'TW': 'Taiwan',
            'TZ': 'Tanzania',
            'UA': 'Ukraine',
            'UG': 'Uganda',
            'UM': 'United States Minor Outlying Islands',
            'US': 'United States',
            'UY': 'Uruguay',
            'UZ': 'Uzbekistan',
            'VA': 'Vatican City',
            'VC': 'Saint Vincent and the Grenadines',
            'VE': 'Venezuela',
            'VG': 'British Virgin Islands',
            'VI': 'United States Virgin Islands',
            'VN': 'Vietnam',
            'VU': 'Vanuatu',
            'WF': 'Wallis and Futuna',
            'WS': 'Samoa',
            'XK': 'Kosovo',
            'YE': 'Yemen',
            'YT': 'Mayotte',
            'ZA': 'South Africa',
            'ZM': 'Zambia',
            'ZW': 'Zimbabwe'
        }
        
        return countryMapping[countryCode] || null
    }

    /**
     * Obtiene el nombre del estado por su ID y código de país
     */
    getStateNameById(stateId, countryCode) {
        const staticStates = this.getStaticStates(countryCode)
        const state = staticStates.find(s => s.id === stateId || s.code === stateId)
        return state ? state.name : null
    }

    /**
     * Limpia el nombre del estado removiendo palabras innecesarias
     * Solo para mostrar al usuario, mantiene el nombre original para las API
     */
    cleanStateName(stateName) {
        if (!stateName) return stateName
        
        // Lista de palabras a remover (case insensitive)
        const wordsToRemove = [
            'Department',
            'Departamento',
            'Prefecture', 
            'Prefectura',
            'Region',
            'Región',
            'Province',
            'Provincia',
            'State of',
            'Estado de'
        ]
        
        let cleanName = stateName.trim()
        
        // Remover palabras específicas
        wordsToRemove.forEach(word => {
            // Remover al final del nombre
            const endPattern = new RegExp(`\\s+${word}$`, 'gi')
            cleanName = cleanName.replace(endPattern, '')
            
            // Remover al inicio del nombre
            const startPattern = new RegExp(`^${word}\\s+`, 'gi')
            cleanName = cleanName.replace(startPattern, '')
        })
        
        return cleanName.trim()
    }

    /**
     * Obtiene los datos estáticos de países (fallback)
     */
    getStaticCountries() {
        return [
            { code: 'US', name: 'Estados Unidos', iso3: 'USA' },
            { code: 'CA', name: 'Canadá', iso3: 'CAN' },
            { code: 'MX', name: 'México', iso3: 'MEX' },
            { code: 'BR', name: 'Brasil', iso3: 'BRA' },
            { code: 'AR', name: 'Argentina', iso3: 'ARG' },
            { code: 'CO', name: 'Colombia', iso3: 'COL' },
            { code: 'PE', name: 'Perú', iso3: 'PER' },
            { code: 'CL', name: 'Chile', iso3: 'CHL' },
            { code: 'ES', name: 'España', iso3: 'ESP' },
            { code: 'FR', name: 'Francia', iso3: 'FRA' },
            { code: 'DE', name: 'Alemania', iso3: 'DEU' },
            { code: 'IT', name: 'Italia', iso3: 'ITA' },
            { code: 'GB', name: 'Reino Unido', iso3: 'GBR' },
            { code: 'CN', name: 'China', iso3: 'CHN' },
            { code: 'JP', name: 'Japón', iso3: 'JPN' },
            { code: 'IN', name: 'India', iso3: 'IND' },
            { code: 'AU', name: 'Australia', iso3: 'AUS' }
        ]
    }

    /**
     * Estados/regiones/departamentos correctos para países principales
     */
    getStaticStates(countryCode) {
        const statesData = {
            'US': [
                { id: 'AL', name: 'Alabama', originalName: 'Alabama', code: 'AL' },
                { id: 'CA', name: 'California', originalName: 'California', code: 'CA' },
                { id: 'FL', name: 'Florida', originalName: 'Florida', code: 'FL' },
                { id: 'GA', name: 'Georgia', originalName: 'Georgia', code: 'GA' },
                { id: 'IL', name: 'Illinois', originalName: 'Illinois', code: 'IL' },
                { id: 'NY', name: 'New York', originalName: 'New York', code: 'NY' },
                { id: 'TX', name: 'Texas', originalName: 'Texas', code: 'TX' },
                { id: 'WA', name: 'Washington', originalName: 'Washington', code: 'WA' }
            ],
            'MX': [
                { id: 'AGU', name: 'Aguascalientes', code: 'AGU' },
                { id: 'BC', name: 'Baja California', code: 'BC' },
                { id: 'BCS', name: 'Baja California Sur', code: 'BCS' },
                { id: 'CAM', name: 'Campeche', code: 'CAM' },
                { id: 'CHIS', name: 'Chiapas', code: 'CHIS' },
                { id: 'CHIH', name: 'Chihuahua', code: 'CHIH' },
                { id: 'CDMX', name: 'Ciudad de México', code: 'CDMX' },
                { id: 'COAH', name: 'Coahuila', code: 'COAH' },
                { id: 'COL', name: 'Colima', code: 'COL' },
                { id: 'DUR', name: 'Durango', code: 'DUR' },
                { id: 'GTO', name: 'Guanajuato', code: 'GTO' },
                { id: 'GRO', name: 'Guerrero', code: 'GRO' },
                { id: 'HID', name: 'Hidalgo', code: 'HID' },
                { id: 'JAL', name: 'Jalisco', code: 'JAL' },
                { id: 'MEX', name: 'México', code: 'MEX' },
                { id: 'MICH', name: 'Michoacán', code: 'MICH' },
                { id: 'MOR', name: 'Morelos', code: 'MOR' },
                { id: 'NAY', name: 'Nayarit', code: 'NAY' },
                { id: 'NL', name: 'Nuevo León', code: 'NL' },
                { id: 'OAX', name: 'Oaxaca', code: 'OAX' },
                { id: 'PUE', name: 'Puebla', code: 'PUE' },
                { id: 'QRO', name: 'Querétaro', code: 'QRO' },
                { id: 'QROO', name: 'Quintana Roo', code: 'QROO' },
                { id: 'SLP', name: 'San Luis Potosí', code: 'SLP' },
                { id: 'SIN', name: 'Sinaloa', code: 'SIN' },
                { id: 'SON', name: 'Sonora', code: 'SON' },
                { id: 'TAB', name: 'Tabasco', code: 'TAB' },
                { id: 'TAMPS', name: 'Tamaulipas', code: 'TAMPS' },
                { id: 'TLAX', name: 'Tlaxcala', code: 'TLAX' },
                { id: 'VER', name: 'Veracruz', code: 'VER' },
                { id: 'YUC', name: 'Yucatán', code: 'YUC' },
                { id: 'ZAC', name: 'Zacatecas', code: 'ZAC' }
            ],
            'CO': [
                { id: 'AMA', name: 'Amazonas', code: 'AMA' },
                { id: 'ANT', name: 'Antioquia', code: 'ANT' },
                { id: 'ARA', name: 'Arauca', code: 'ARA' },
                { id: 'ATL', name: 'Atlántico', code: 'ATL' },
                { id: 'BOL', name: 'Bolívar', code: 'BOL' },
                { id: 'BOY', name: 'Boyacá', code: 'BOY' },
                { id: 'CAL', name: 'Caldas', code: 'CAL' },
                { id: 'CAQ', name: 'Caquetá', code: 'CAQ' },
                { id: 'CAS', name: 'Casanare', code: 'CAS' },
                { id: 'CAU', name: 'Cauca', code: 'CAU' },
                { id: 'CES', name: 'Cesar', code: 'CES' },
                { id: 'CHO', name: 'Chocó', code: 'CHO' },
                { id: 'COR', name: 'Córdoba', code: 'COR' },
                { id: 'CUN', name: 'Cundinamarca', code: 'CUN' },
                { id: 'DC', name: 'Distrito Capital (Bogotá)', code: 'DC' },
                { id: 'GUA', name: 'Guainía', code: 'GUA' },
                { id: 'GUV', name: 'Guaviare', code: 'GUV' },
                { id: 'HUI', name: 'Huila', code: 'HUI' },
                { id: 'LAG', name: 'La Guajira', code: 'LAG' },
                { id: 'MAG', name: 'Magdalena', code: 'MAG' },
                { id: 'MET', name: 'Meta', code: 'MET' },
                { id: 'NAR', name: 'Nariño', code: 'NAR' },
                { id: 'NSA', name: 'Norte de Santander', code: 'NSA' },
                { id: 'PUT', name: 'Putumayo', code: 'PUT' },
                { id: 'QUI', name: 'Quindío', code: 'QUI' },
                { id: 'RIS', name: 'Risaralda', code: 'RIS' },
                { id: 'SAP', name: 'San Andrés y Providencia', code: 'SAP' },
                { id: 'SAN', name: 'Santander', code: 'SAN' },
                { id: 'SUC', name: 'Sucre', code: 'SUC' },
                { id: 'TOL', name: 'Tolima', code: 'TOL' },
                { id: 'VAC', name: 'Valle del Cauca', code: 'VAC' },
                { id: 'VAU', name: 'Vaupés', code: 'VAU' },
                { id: 'VIC', name: 'Vichada', code: 'VIC' }
            ],
            'AR': [
                { id: 'CABA', name: 'Ciudad Autónoma de Buenos Aires', code: 'CABA' },
                { id: 'BA', name: 'Provincia de Buenos Aires', code: 'BA' },
                { id: 'CAT', name: 'Catamarca', code: 'CAT' },
                { id: 'CHA', name: 'Chaco', code: 'CHA' },
                { id: 'CHU', name: 'Chubut', code: 'CHU' },
                { id: 'COR', name: 'Córdoba', code: 'COR' },
                { id: 'COR', name: 'Corrientes', code: 'COR' },
                { id: 'ER', name: 'Entre Ríos', code: 'ER' },
                { id: 'FOR', name: 'Formosa', code: 'FOR' },
                { id: 'JUJ', name: 'Jujuy', code: 'JUJ' },
                { id: 'LP', name: 'La Pampa', code: 'LP' },
                { id: 'LR', name: 'La Rioja', code: 'LR' },
                { id: 'MEN', name: 'Mendoza', code: 'MEN' },
                { id: 'MIS', name: 'Misiones', code: 'MIS' },
                { id: 'NEU', name: 'Neuquén', code: 'NEU' },
                { id: 'RN', name: 'Río Negro', code: 'RN' },
                { id: 'SAL', name: 'Salta', code: 'SAL' },
                { id: 'SJ', name: 'San Juan', code: 'SJ' },
                { id: 'SL', name: 'San Luis', code: 'SL' },
                { id: 'SC', name: 'Santa Cruz', code: 'SC' },
                { id: 'SF', name: 'Santa Fe', code: 'SF' },
                { id: 'SE', name: 'Santiago del Estero', code: 'SE' },
                { id: 'TF', name: 'Tierra del Fuego', code: 'TF' },
                { id: 'TUC', name: 'Tucumán', code: 'TUC' }
            ],
            'ES': [
                { id: 'AN', name: 'Andalucía', code: 'AN' },
                { id: 'AR', name: 'Aragón', code: 'AR' },
                { id: 'AS', name: 'Asturias', code: 'AS' },
                { id: 'IB', name: 'Islas Baleares', code: 'IB' },
                { id: 'CN', name: 'Islas Canarias', code: 'CN' },
                { id: 'CB', name: 'Cantabria', code: 'CB' },
                { id: 'CM', name: 'Castilla-La Mancha', code: 'CM' },
                { id: 'CL', name: 'Castilla y León', code: 'CL' },
                { id: 'CT', name: 'Cataluña', code: 'CT' },
                { id: 'EX', name: 'Extremadura', code: 'EX' },
                { id: 'GA', name: 'Galicia', code: 'GA' },
                { id: 'MD', name: 'Madrid', code: 'MD' },
                { id: 'MC', name: 'Murcia', code: 'MC' },
                { id: 'NC', name: 'Navarra', code: 'NC' },
                { id: 'PV', name: 'País Vasco', code: 'PV' },
                { id: 'RI', name: 'La Rioja', code: 'RI' },
                { id: 'VC', name: 'Valencia', code: 'VC' }
            ],
            'PE': [
                { id: 'AMA', name: 'Amazonas', code: 'AMA' },
                { id: 'ANC', name: 'Áncash', code: 'ANC' },
                { id: 'APU', name: 'Apurímac', code: 'APU' },
                { id: 'ARE', name: 'Arequipa', code: 'ARE' },
                { id: 'AYA', name: 'Ayacucho', code: 'AYA' },
                { id: 'CAJ', name: 'Cajamarca', code: 'CAJ' },
                { id: 'CAL', name: 'Callao', code: 'CAL' },
                { id: 'CUS', name: 'Cusco', code: 'CUS' },
                { id: 'HUV', name: 'Huancavelica', code: 'HUV' },
                { id: 'HUC', name: 'Huánuco', code: 'HUC' },
                { id: 'ICA', name: 'Ica', code: 'ICA' },
                { id: 'JUN', name: 'Junín', code: 'JUN' },
                { id: 'LAL', name: 'La Libertad', code: 'LAL' },
                { id: 'LAM', name: 'Lambayeque', code: 'LAM' },
                { id: 'LIM', name: 'Lima', code: 'LIM' },
                { id: 'LOR', name: 'Loreto', code: 'LOR' },
                { id: 'MDD', name: 'Madre de Dios', code: 'MDD' },
                { id: 'MOQ', name: 'Moquegua', code: 'MOQ' },
                { id: 'PAS', name: 'Pasco', code: 'PAS' },
                { id: 'PIU', name: 'Piura', code: 'PIU' },
                { id: 'PUN', name: 'Puno', code: 'PUN' },
                { id: 'SAM', name: 'San Martín', code: 'SAM' },
                { id: 'TAC', name: 'Tacna', code: 'TAC' },
                { id: 'TUM', name: 'Tumbes', code: 'TUM' },
                { id: 'UCA', name: 'Ucayali', code: 'UCA' }
            ],
            'BR': [
                { id: 'AC', name: 'Acre', code: 'AC' },
                { id: 'AL', name: 'Alagoas', code: 'AL' },
                { id: 'AP', name: 'Amapá', code: 'AP' },
                { id: 'AM', name: 'Amazonas', code: 'AM' },
                { id: 'BA', name: 'Bahía', code: 'BA' },
                { id: 'CE', name: 'Ceará', code: 'CE' },
                { id: 'DF', name: 'Distrito Federal', code: 'DF' },
                { id: 'ES', name: 'Espírito Santo', code: 'ES' },
                { id: 'GO', name: 'Goiás', code: 'GO' },
                { id: 'MA', name: 'Maranhão', code: 'MA' },
                { id: 'MT', name: 'Mato Grosso', code: 'MT' },
                { id: 'MS', name: 'Mato Grosso do Sul', code: 'MS' },
                { id: 'MG', name: 'Minas Gerais', code: 'MG' },
                { id: 'PA', name: 'Pará', code: 'PA' },
                { id: 'PB', name: 'Paraíba', code: 'PB' },
                { id: 'PR', name: 'Paraná', code: 'PR' },
                { id: 'PE', name: 'Pernambuco', code: 'PE' },
                { id: 'PI', name: 'Piauí', code: 'PI' },
                { id: 'RJ', name: 'Río de Janeiro', code: 'RJ' },
                { id: 'RN', name: 'Rio Grande do Norte', code: 'RN' },
                { id: 'RS', name: 'Rio Grande do Sul', code: 'RS' },
                { id: 'RO', name: 'Rondônia', code: 'RO' },
                { id: 'RR', name: 'Roraima', code: 'RR' },
                { id: 'SC', name: 'Santa Catarina', code: 'SC' },
                { id: 'SP', name: 'São Paulo', code: 'SP' },
                { id: 'SE', name: 'Sergipe', code: 'SE' },
                { id: 'TO', name: 'Tocantins', code: 'TO' }
            ],
            'CL': [
                { id: 'XV', name: 'Arica y Parinacota', code: 'XV' },
                { id: 'I', name: 'Tarapacá', code: 'I' },
                { id: 'II', name: 'Antofagasta', code: 'II' },
                { id: 'III', name: 'Atacama', code: 'III' },
                { id: 'IV', name: 'Coquimbo', code: 'IV' },
                { id: 'V', name: 'Valparaíso', code: 'V' },
                { id: 'RM', name: 'Región Metropolitana', code: 'RM' },
                { id: 'VI', name: 'O\'Higgins', code: 'VI' },
                { id: 'VII', name: 'Maule', code: 'VII' },
                { id: 'VIII', name: 'Biobío', code: 'VIII' },
                { id: 'IX', name: 'Araucanía', code: 'IX' },
                { id: 'XIV', name: 'Los Ríos', code: 'XIV' },
                { id: 'X', name: 'Los Lagos', code: 'X' },
                { id: 'XI', name: 'Aysén', code: 'XI' },
                { id: 'XII', name: 'Magallanes', code: 'XII' }
            ]
        }
        
        // Asegurar que cada estado tenga originalName
        const states = statesData[countryCode] || []
        return states.map(state => ({
            ...state,
            originalName: state.originalName || state.name
        }))
    }

    /**
     * Ciudades principales para cada estado/departamento/región
     */
    getStaticCities(countryCode, stateCode) {
        const citiesData = {
            'MX': {
                'CDMX': [
                    { id: 'mx_cdmx_1', name: 'Ciudad de México', population: 9200000 }
                ],
                'JAL': [
                    { id: 'mx_jal_1', name: 'Guadalajara', population: 1500000 },
                    { id: 'mx_jal_2', name: 'Zapopan', population: 1400000 },
                    { id: 'mx_jal_3', name: 'Tlaquepaque', population: 650000 },
                    { id: 'mx_jal_4', name: 'Puerto Vallarta', population: 290000 },
                    { id: 'mx_jal_5', name: 'Tonalá', population: 540000 }
                ],
                'NL': [
                    { id: 'mx_nl_1', name: 'Monterrey', population: 1140000 },
                    { id: 'mx_nl_2', name: 'Guadalupe', population: 700000 },
                    { id: 'mx_nl_3', name: 'San Nicolás de los Garza', population: 430000 },
                    { id: 'mx_nl_4', name: 'Apodaca', population: 530000 }
                ],
                'BC': [
                    { id: 'mx_bc_1', name: 'Tijuana', population: 1810000 },
                    { id: 'mx_bc_2', name: 'Mexicali', population: 1000000 },
                    { id: 'mx_bc_3', name: 'Ensenada', population: 520000 }
                ],
                'PUE': [
                    { id: 'mx_pue_1', name: 'Puebla de Zaragoza', population: 1540000 },
                    { id: 'mx_pue_2', name: 'Tehuacán', population: 320000 },
                    { id: 'mx_pue_3', name: 'San Martín Texmelucan', population: 150000 }
                ],
                'VER': [
                    { id: 'mx_ver_1', name: 'Veracruz', population: 610000 },
                    { id: 'mx_ver_2', name: 'Xalapa', population: 490000 },
                    { id: 'mx_ver_3', name: 'Coatzacoalcos', population: 320000 }
                ]
            },
            'CO': {
                'DC': [
                    { id: 'co_dc_1', name: 'Bogotá', population: 7400000 }
                ],
                'ANT': [
                    { id: 'co_ant_1', name: 'Medellín', population: 2500000 },
                    { id: 'co_ant_2', name: 'Bello', population: 500000 },
                    { id: 'co_ant_3', name: 'Itagüí', population: 280000 },
                    { id: 'co_ant_4', name: 'Envigado', population: 230000 },
                    { id: 'co_ant_5', name: 'Apartadó', population: 190000 }
                ],
                'VAC': [
                    { id: 'co_vac_1', name: 'Cali', population: 2200000 },
                    { id: 'co_vac_2', name: 'Palmira', population: 300000 },
                    { id: 'co_vac_3', name: 'Buenaventura', population: 400000 },
                    { id: 'co_vac_4', name: 'Tulúa', population: 230000 }
                ],
                'ATL': [
                    { id: 'co_atl_1', name: 'Barranquilla', population: 1200000 },
                    { id: 'co_atl_2', name: 'Soledad', population: 650000 },
                    { id: 'co_atl_3', name: 'Malambo', population: 130000 }
                ],
                'SAN': [
                    { id: 'co_san_1', name: 'Bucaramanga', population: 610000 },
                    { id: 'co_san_2', name: 'Floridablanca', population: 270000 },
                    { id: 'co_san_3', name: 'Girón', population: 180000 }
                ]
            },
            'ES': {
                'MD': [
                    { id: 'es_md_1', name: 'Madrid', population: 3200000 },
                    { id: 'es_md_2', name: 'Móstoles', population: 210000 },
                    { id: 'es_md_3', name: 'Alcalá de Henares', population: 195000 },
                    { id: 'es_md_4', name: 'Getafe', population: 180000 },
                    { id: 'es_md_5', name: 'Leganés', population: 190000 }
                ],
                'CT': [
                    { id: 'es_ct_1', name: 'Barcelona', population: 1600000 },
                    { id: 'es_ct_2', name: 'Hospitalet de Llobregat', population: 260000 },
                    { id: 'es_ct_3', name: 'Terrassa', population: 220000 },
                    { id: 'es_ct_4', name: 'Sabadell', population: 215000 },
                    { id: 'es_ct_5', name: 'Lleida', population: 140000 }
                ],
                'AN': [
                    { id: 'es_an_1', name: 'Sevilla', population: 690000 },
                    { id: 'es_an_2', name: 'Málaga', population: 570000 },
                    { id: 'es_an_3', name: 'Granada', population: 230000 },
                    { id: 'es_an_4', name: 'Córdoba', population: 320000 },
                    { id: 'es_an_5', name: 'Almería', population: 200000 }
                ],
                'VC': [
                    { id: 'es_vc_1', name: 'Valencia', population: 790000 },
                    { id: 'es_vc_2', name: 'Alicante', population: 330000 },
                    { id: 'es_vc_3', name: 'Elche', population: 230000 },
                    { id: 'es_vc_4', name: 'Castellón', population: 170000 }
                ]
            },
            'AR': {
                'CABA': [
                    { id: 'ar_caba_1', name: 'Ciudad Autónoma de Buenos Aires', population: 2890000 }
                ],
                'BA': [
                    { id: 'ar_ba_1', name: 'La Plata', population: 650000 },
                    { id: 'ar_ba_2', name: 'Mar del Plata', population: 600000 },
                    { id: 'ar_ba_3', name: 'Bahía Blanca', population: 300000 },
                    { id: 'ar_ba_4', name: 'San Isidro', population: 290000 },
                    { id: 'ar_ba_5', name: 'Tandil', population: 130000 }
                ],
                'COR': [
                    { id: 'ar_cor_1', name: 'Córdoba', population: 1330000 },
                    { id: 'ar_cor_2', name: 'Villa Carlos Paz', population: 60000 },
                    { id: 'ar_cor_3', name: 'Río Cuarto', population: 160000 }
                ],
                'SF': [
                    { id: 'ar_sf_1', name: 'Rosario', population: 950000 },
                    { id: 'ar_sf_2', name: 'Santa Fe', population: 390000 },
                    { id: 'ar_sf_3', name: 'Rafaela', population: 100000 }
                ],
                'MEN': [
                    { id: 'ar_men_1', name: 'Mendoza', population: 115000 },
                    { id: 'ar_men_2', name: 'San Rafael', population: 180000 },
                    { id: 'ar_men_3', name: 'Godoy Cruz', population: 190000 }
                ]
            },
            'PE': {
                'LIM': [
                    { id: 'pe_lim_1', name: 'Lima', population: 10000000 },
                    { id: 'pe_lim_2', name: 'Callao', population: 1000000 }
                ],
                'ARE': [
                    { id: 'pe_are_1', name: 'Arequipa', population: 870000 },
                    { id: 'pe_are_2', name: 'Cerro Colorado', population: 150000 }
                ],
                'LAL': [
                    { id: 'pe_lal_1', name: 'Trujillo', population: 920000 },
                    { id: 'pe_lal_2', name: 'Chimbote', population: 370000 }
                ],
                'CUS': [
                    { id: 'pe_cus_1', name: 'Cusco', population: 430000 },
                    { id: 'pe_cus_2', name: 'Sicuani', population: 60000 }
                ]
            },
            'US': {
                'CA': [
                    { id: 'us_ca_1', name: 'Los Angeles', population: 4000000 },
                    { id: 'us_ca_2', name: 'San Francisco', population: 880000 },
                    { id: 'us_ca_3', name: 'San Diego', population: 1400000 },
                    { id: 'us_ca_4', name: 'Sacramento', population: 500000 },
                    { id: 'us_ca_5', name: 'San José', population: 1030000 }
                ],
                'NY': [
                    { id: 'us_ny_1', name: 'New York City', population: 8400000 },
                    { id: 'us_ny_2', name: 'Buffalo', population: 260000 },
                    { id: 'us_ny_3', name: 'Rochester', population: 210000 },
                    { id: 'us_ny_4', name: 'Albany', population: 100000 }
                ],
                'FL': [
                    { id: 'us_fl_1', name: 'Miami', population: 470000 },
                    { id: 'us_fl_2', name: 'Orlando', population: 280000 },
                    { id: 'us_fl_3', name: 'Tampa', population: 390000 },
                    { id: 'us_fl_4', name: 'Jacksonville', population: 950000 }
                ],
                'TX': [
                    { id: 'us_tx_1', name: 'Houston', population: 2300000 },
                    { id: 'us_tx_2', name: 'San Antonio', population: 1530000 },
                    { id: 'us_tx_3', name: 'Dallas', population: 1340000 },
                    { id: 'us_tx_4', name: 'Austin', population: 970000 }
                ]
            }
        }
        
        return citiesData[countryCode]?.[stateCode] || []
    }

    /**
     * Obtiene información completa de ubicación basada en códigos
     */
    async getLocationInfo(countryCode, stateCode, cityId) {
        try {
            const countries = await this.getCountries()
            const country = countries.find(c => c.code === countryCode)
            
            if (!country) return null

            let state = null
            let city = null

            if (stateCode) {
                const states = await this.getStates(countryCode)
                state = states.find(s => s.code === stateCode)
            }

            if (cityId) {
                const response = await axios.get(
                    `${this.geonamesAPI}/getJSON?geonameId=${cityId}&username=${this.geonamesUsername}`
                )
                if (response.data) {
                    city = {
                        id: response.data.geonameId,
                        name: response.data.name
                    }
                }
            }

            return {
                country,
                state,
                city
            }
        } catch (error) {
            console.error('Error getting location info:', error)
            return null
        }
    }
}

export default new LocationService()
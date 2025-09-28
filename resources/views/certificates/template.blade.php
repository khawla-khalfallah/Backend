<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificat - {{ $platform_name }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Open+Sans:wght@400;600&display=swap');
        
        body {
            font-family: 'Open Sans', sans-serif;
            margin: 0;
            padding: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            box-sizing: border-box;
        }
        
        .certificate-container {
            background: white;
            border-radius: 20px;
            padding: 60px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .certificate-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #f5576c);
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }
        
        .platform-logo {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
            letter-spacing: 2px;
        }
        
        .platform-tagline {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 30px;
        }
        
        .certificate-title {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            color: #2c3e50;
            margin-bottom: 20px;
            font-weight: 400;
            letter-spacing: 1px;
        }
        
        .certificate-subtitle {
            font-size: 18px;
            color: #7f8c8d;
            margin-bottom: 40px;
        }
        
        .recipient-section {
            text-align: center;
            margin: 50px 0;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 15px;
            border-left: 5px solid #667eea;
        }
        
        .recipient-name {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .formation-info {
            margin: 40px 0;
            text-align: center;
        }
        
        .formation-title {
            font-size: 24px;
            color: #34495e;
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .achievement-text {
            font-size: 16px;
            color: #5a6c7d;
            line-height: 1.6;
            margin: 20px 0;
        }
        
        .score-section {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
            text-align: center;
        }
        
        .score-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .score-value {
            font-size: 28px;
            font-weight: 700;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin: 40px 0;
        }
        
        .detail-item {
            text-align: center;
        }
        
        .detail-label {
            font-size: 12px;
            color: #95a5a6;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }
        
        .detail-value {
            font-size: 16px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .signature-section {
            margin-top: 60px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: end;
        }
        
        .signature-block {
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 2px solid #bdc3c7;
            height: 60px;
            margin-bottom: 10px;
            position: relative;
        }
        
        .signature-stamp {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 80px;
            height: 80px;
            border: 3px solid #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(102, 126, 234, 0.1);
            font-size: 10px;
            color: #667eea;
            font-weight: 700;
            text-align: center;
            line-height: 1.2;
        }
        
        .signature-label {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 10px;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            font-size: 12px;
            color: #95a5a6;
        }
        
        .decorative-border {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 2px solid #ecf0f1;
            border-radius: 15px;
            pointer-events: none;
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(102, 126, 234, 0.03);
            font-weight: 700;
            pointer-events: none;
            z-index: 0;
        }
        
        .content {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="decorative-border"></div>
        <div class="watermark">{{ $platform_name }}</div>
        
        <div class="content">
            <div class="header">
                <div class="platform-logo">{{ $platform_name }}</div>
                <div class="platform-tagline">Plateforme de Formation en Ligne</div>
                <h1 class="certificate-title">Certificat</h1>
                <p class="certificate-subtitle">de Réussite</p>
            </div>
            
            <div class="recipient-section">
                <div style="font-size: 16px; color: #7f8c8d; margin-bottom: 15px;">
                    Ce certificat est décerné à
                </div>
                <div class="recipient-name">
                    {{ $user->prenom }} {{ $user->nom }}
                </div>
                <div style="font-size: 14px; color: #95a5a6; margin-top: 10px;">
                    ID Apprenant: #{{ $apprenant->user_id }}
                </div>
            </div>
            
            <div class="formation-info">
                <div style="font-size: 16px; color: #7f8c8d; margin-bottom: 10px;">
                    Pour avoir complété avec succès la formation
                </div>
                <div class="formation-title">{{ $formation->titre }}</div>
                
                <div class="achievement-text">
                    {{ $certificat->titre_certification }}
                </div>
                
                @if($certificat->note_examen)
                <div class="score-section">
                    <div class="score-label">Note obtenue à l'examen</div>
                    <div class="score-value">{{ number_format($certificat->note_examen, 1) }}/20</div>
                </div>
                @endif
            </div>
            
            <div class="details-grid">
                <div class="detail-item">
                    <div class="detail-label">Date d'obtention</div>
                    <div class="detail-value">{{ $certificat->date_obtention->format('d F Y') }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Formateur</div>
                    <div class="detail-value">{{ $formateur->prenom }} {{ $formateur->nom }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Durée de formation</div>
                    <div class="detail-value">{{ $formation->duree }} heures</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Certificat N°</div>
                    <div class="detail-value">#{{ str_pad($certificat->id, 6, '0', STR_PAD_LEFT) }}</div>
                </div>
            </div>
            
            <div class="signature-section">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-label">
                        <strong>{{ $formateur->prenom }} {{ $formateur->nom }}</strong><br>
                        Formateur Certifié
                    </div>
                </div>
                <div class="signature-block">
                    <div class="signature-line">
                        <div class="signature-stamp">
                            {{ $platform_name }}<br>
                            OFFICIEL
                        </div>
                    </div>
                    <div class="signature-label">
                        <strong>{{ $platform_name }}</strong><br>
                        Plateforme Certifiée
                    </div>
                </div>
            </div>
            
            <div class="footer">
                Ce certificat a été généré le {{ $date_generation->format('d/m/Y à H:i') }} sur la plateforme {{ $platform_name }}.<br>
                Vérifiez l'authenticité de ce certificat sur notre site web avec le code: {{ strtoupper(substr(md5($certificat->id . $certificat->created_at), 0, 8)) }}
            </div>
        </div>
    </div>
</body>
</html>
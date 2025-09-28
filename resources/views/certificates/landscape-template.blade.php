<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificat - {{ $platform_name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm 20mm;
        }
        
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 100%;
            height: 210mm; /* A4 landscape height */
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .certificate {
            background: white;
            width: 267mm; /* A4 landscape width minus margins */
            height: 180mm; /* A4 landscape height minus margins */
            padding: 25mm 30mm;
            border-radius: 8px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            text-align: center;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
        }
        
        .certificate::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.05) 0%, transparent 50%),
                       radial-gradient(circle at 80% 20%, rgba(118, 75, 162, 0.05) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .content {
            position: relative;
            z-index: 1;
        }
        
        .header {
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .platform-name {
            font-size: 28px;
            color: #667eea;
            font-weight: bold;
            margin: 0;
            letter-spacing: 2px;
        }
        
        .platform-subtitle {
            font-size: 12px;
            color: #666;
            margin: 5px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .certificate-title {
            font-size: 28px;
            color: #2c3e50;
            margin: 15px 0;
            font-weight: bold;
            letter-spacing: 3px;
            text-transform: uppercase;
        }
        
        .recipient-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px 30px;
            margin: 15px 0;
            border-radius: 12px;
            border: 2px solid #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
        }
        
        .recipient-text {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
            font-style: italic;
        }
        
        .recipient-name {
            font-size: 26px;
            color: #2c3e50;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .achievement-section {
            margin: 20px 0;
        }
        
        .achievement-text {
            font-size: 16px;
            color: #555;
            line-height: 1.3;
            margin: 8px 0;
        }
        
        .formation-title {
            font-size: 20px;
            color: #667eea;
            font-weight: bold;
            margin: 8px 0;
            font-style: italic;
        }
        
        .details-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
            padding: 15px;
            background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .detail-item {
            text-align: center;
            padding: 10px;
        }
        
        .detail-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .detail-value {
            font-size: 15px;
            color: #2c3e50;
            font-weight: bold;
        }
        
        .score-badge {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #667eea;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 30px;
        }
        
        .signature-section {
            text-align: center;
            padding: 10px;
        }
        
        .signature-line {
            width: 120px;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            margin: 15px auto 8px;
            border-radius: 1px;
        }
        
        .signature-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .signature-name {
            font-size: 13px;
            color: #2c3e50;
            font-weight: bold;
            margin-top: 3px;
        }
        
        .seal {
            width: 70px;
            height: 70px;
            border: 3px solid #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .seal-text {
            font-size: 9px;
            color: #667eea;
            font-weight: bold;
            text-align: center;
            line-height: 1.1;
            letter-spacing: 0.5px;
        }
        
        .certificate-id {
            font-size: 10px;
            color: #999;
            margin-top: 15px;
        }
        
        /* Decorative elements */
        .corner-decoration {
            position: absolute;
            width: 30px;
            height: 30px;
            border: 2px solid #667eea;
        }
        
        .corner-decoration.top-left {
            top: 15px;
            left: 15px;
            border-right: none;
            border-bottom: none;
        }
        
        .corner-decoration.top-right {
            top: 15px;
            right: 15px;
            border-left: none;
            border-bottom: none;
        }
        
        .corner-decoration.bottom-left {
            bottom: 15px;
            left: 15px;
            border-right: none;
            border-top: none;
        }
        
        .corner-decoration.bottom-right {
            bottom: 15px;
            right: 15px;
            border-left: none;
            border-top: none;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <!-- Decorative corners -->
        <div class="corner-decoration top-left"></div>
        <div class="corner-decoration top-right"></div>
        <div class="corner-decoration bottom-left"></div>
        <div class="corner-decoration bottom-right"></div>
        
        <div class="content">
            <!-- Header -->
            <div class="header">
                <h1 class="platform-name">{{ $platform_name }}</h1>
                <p class="platform-subtitle">Plateforme de Formation en Ligne</p>
            </div>
            
            <!-- Certificate Title -->
            <h2 class="certificate-title">CERTIFICAT DE RÉUSSITE</h2>
            
            <!-- Recipient -->
            <div class="recipient-section">
                <p class="recipient-text">Ce certificat est décerné à</p>
                <h3 class="recipient-name">{{ $user->prenom }} {{ $user->nom }}</h3>
            </div>
            
            <!-- Achievement -->
            <div class="achievement-section">
                <p class="achievement-text">
                    Pour avoir complété avec succès la formation
                </p>
                <h4 class="formation-title">{{ $formation->titre }}</h4>
                <p class="achievement-text">{{ $certificat->titre_certification }}</p>
            </div>
            
            <!-- Details -->
            <div class="details-row">
                <div class="detail-item">
                    <div class="detail-label">Date d'obtention</div>
                    <div class="detail-value">{{ $certificat->date_obtention->format('d/m/Y') }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Note obtenue</div>
                    <div class="detail-value">
                        <span class="score-badge">{{ number_format($certificat->note_examen, 1) }}/20</span>
                    </div>
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
            
            <!-- Footer with signatures -->
            <div class="footer">
                <div class="signature-section">
                    <div class="signature-line"></div>
                    <div class="signature-label">Formateur</div>
                    <div class="signature-name">{{ $formateur->prenom }} {{ $formateur->nom }}</div>
                </div>
                
                <div class="seal">
                    <div class="seal-text">
                        {{ $platform_name }}<br>
                        OFFICIEL
                    </div>
                </div>
                
                <div class="signature-section">
                    <div class="signature-line"></div>
                    <div class="signature-label">Plateforme</div>
                    <div class="signature-name">{{ $platform_name }}</div>
                </div>
            </div>
            
            <!-- Certificate ID and verification -->
            <div class="certificate-id">
                Généré le {{ $date_generation->format('d/m/Y à H:i') }} | 
                Code de vérification: {{ strtoupper(substr(md5($certificat->id . $date_generation->format('Y-m-d')), 0, 8)) }}
            </div>
        </div>
    </div>
</body>
</html>
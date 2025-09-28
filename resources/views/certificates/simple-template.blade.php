<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificat - {{ $platform_name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: white;
            width: 100%;
            height: 100%;
        }
        
        .certificate {
            background: white;
            width: 100%;
            height: 100vh;
            max-width: 277mm;
            max-height: 190mm;
            position: relative;
            box-sizing: border-box;
            border: 2px solid #2c3e50;
            page-break-inside: avoid;
        }
        
        .certificate::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border: 1px solid #bdc3c7;
            pointer-events: none;
        }
        
        .header-section {
            position: absolute;
            top: 25px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            width: 90%;
        }
        
        .platform-name {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0 0 3px 0;
        }
        
        .platform-subtitle {
            font-size: 16px;
            color: #7f8c8d;
            margin: 0 0 50px 0;
            text-transform: uppercase;
        }
        
        .certificate-title {
            font-size: 42px;
            color: #2c3e50;
            font-weight: bold;
            margin: 0;
            letter-spacing: 3px;
            text-transform: uppercase;
        }
        
        .content {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            width: 70%;
        }
        
        .awarded-to {
            font-size: 22px;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }
        
        .recipient-name {
            font-size: 36px;
            color: #2c3e50;
            font-weight: bold;
            margin: 0 0 15px 0;
            text-transform: uppercase;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 5px;
            display: inline-block;
        }
        
        .completion-text {
            font-size: 22px;
            color: #2c3e50;
            margin: 0 0 8px 0;
        }
        
        .course-name {
            font-size: 30px;
            color: #2c3e50;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        
        .bottom-section {
            position: absolute;
            bottom: 15px;
            left: 20px;
            right: 20px;
            display: flex;
            justify-content: space-between;
            align-items: end;
        }
        
        .date-section {
            text-align: left;
        }
        
        .date-value {
            font-size: 24px;
            color: #2c3e50;
            font-weight: bold;
            margin: 0 0 3px 0;
        }
        
        .date-label {
            font-size: 20px;
            color: #7f8c8d;
            margin: 0;
        }
        
        .signature-section {
            text-align: right;
        }
        
        .signature-line {
            width: 120px;
            height: 2px;
            background: #2c3e50;
            margin: 0 0 5px auto;
        }
        
        .signature-title {
            font-size: 20px;
            color: #2c3e50;
            font-weight: bold;
            margin: 0 0 3px 0;
        }
        
        .certificate-ref {
            font-size: 18px;
            color: #95a5a6;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="header-section">
            <div class="platform-name">{{ $platform_name }}</div>
            <div class="platform-subtitle">Plateforme de Formation en Ligne</div>
            <div class="certificate-title">CERTIFICAT DE RÉUSSITE</div>
        </div>
        
        <div class="content">
            <div class="awarded-to">Ce certificat est décerné à</div>
            <div class="recipient-name">{{ $user->prenom }} {{ $user->nom }}</div>
            <div class="completion-text">Pour avoir complété avec succès la formation</div>
            <div class="course-name">{{ $formation->titre }}</div>
        </div>
        
        <div class="bottom-section">
            <div class="date-section">
                <div class="date-value">{{ $certificat->date_obtention->format('d/m/Y') }}</div>
                                <p class="date-label">Date d'émission</p>
            </div>
            
            <div class="signature-section">
                <div class="signature-line"></div>
                <div class="signature-title">{{ $platform_name }}</div>
                <div class="certificate-ref">Réf: #{{ str_pad($certificat->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>
    </div>
</body>
</html>
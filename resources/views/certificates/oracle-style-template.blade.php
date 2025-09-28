<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificat - {{ $platform_name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: white;
            width: 297mm;
            height: 210mm;
        }
        
        .certificate {
            background: white;
            width: 297mm;
            height: 210mm;
            padding: 0;
            position: relative;
            border: 3px solid #34495e;
            box-sizing: border-box;
        }
        
        /* Inner border */
        .certificate::before {
            content: '';
            position: absolute;
            top: 8px;
            left: 8px;
            right: 8px;
            bottom: 8px;
            border: 1px solid #bdc3c7;
            pointer-events: none;
        }
        
        /* Header section - top left */
        .header {
            position: absolute;
            top: 25px;
            left: 25px;
            text-align: left;
        }
        
        .platform-name {
            font-size: 16px;
            font-weight: bold;
            color: #e74c3c;
            margin: 0;
            letter-spacing: 0.5px;
        }
        
        .platform-subtitle {
            font-size: 9px;
            color: #34495e;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            font-weight: normal;
        }
        
        /* Main title - center top */
        .main-title {
            position: absolute;
            top: 25px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }
        
        .certificate-title {
            font-size: 24px;
            color: #34495e;
            font-weight: bold;
            margin: 0;
            letter-spacing: 2px;
        }
        
        .recognition-subtitle {
            font-size: 12px;
            color: #7f8c8d;
            margin: 3px 0 0 0;
            font-weight: normal;
            font-style: italic;
        }
        
        /* Main content - center */
        .content-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            width: 70%;
        }
        
        .recipient-intro {
            font-size: 14px;
            color: #34495e;
            margin-bottom: 12px;
        }
        
        .recipient-name {
            font-size: 28px;
            color: #34495e;
            font-weight: bold;
            margin: 8px 0 20px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .achievement-text {
            font-size: 14px;
            color: #34495e;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .formation-title {
            font-size: 22px;
            color: #34495e;
            font-weight: bold;
            margin: 12px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .certificate-description {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 8px;
            font-style: italic;
        }
        
        /* Bottom left - Date */
        .date-section {
            position: absolute;
            bottom: 35px;
            left: 25px;
            text-align: left;
        }
        
        .date-value {
            font-size: 14px;
            color: #34495e;
            font-weight: bold;
            margin: 0;
        }
        
        .date-label {
            font-size: 10px;
            color: #7f8c8d;
            margin: 2px 0 0 0;
        }
        
        /* Bottom right - Signature and details */
        .signature-section {
            position: absolute;
            bottom: 25px;
            right: 25px;
            text-align: center;
            width: 180px;
        }
        
        .signature-image {
            width: 100px;
            height: 30px;
            border-bottom: 1px solid #34495e;
            margin: 0 auto 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Brush Script MT', cursive;
            font-size: 16px;
            color: #34495e;
        }
        
        .signature-name {
            font-size: 12px;
            color: #34495e;
            font-weight: bold;
            margin: 4px 0 2px 0;
        }
        
        .signature-title {
            font-size: 10px;
            color: #7f8c8d;
            margin: 0 0 8px 0;
        }
        
        .certificate-details {
            font-size: 8px;
            color: #95a5a6;
            line-height: 1.1;
        }
        
        /* Score badge */
        .score-info {
            position: absolute;
            top: 25px;
            right: 25px;
            text-align: center;
            background: #ecf0f1;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #d5dbdb;
        }
        
        .score-label {
            font-size: 10px;
            color: #7f8c8d;
            margin: 0 0 2px 0;
        }
        
        .score-value {
            font-size: 14px;
            color: #27ae60;
            font-weight: bold;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <!-- Header - Top Left -->
        <div class="header">
            <h1 class="platform-name">{{ $platform_name }}</h1>
            <p class="platform-subtitle">Plateforme de Formation en Ligne</p>
        </div>
        
        <!-- Main Title - Center Top -->
        <div class="main-title">
            <h2 class="certificate-title">CERTIFICAT DE RÉUSSITE</h2>
            <p class="recognition-subtitle">Certificate of Recognition</p>
        </div>
        
        <!-- Score Info - Top Right -->
        <div class="score-info">
            <p class="score-label">Note obtenue</p>
            <p class="score-value">{{ number_format($certificat->note_examen, 1) }}/20</p>
        </div>
        
        <!-- Main Content - Center -->
        <div class="content-center">
            <p class="recipient-intro">Ce certificat est décerné à</p>
            <h3 class="recipient-name">{{ $user->prenom }} {{ $user->nom }}</h3>
            
            <p class="achievement-text">Pour avoir complété avec succès la formation</p>
            <h4 class="formation-title">{{ $formation->titre }}</h4>
            <p class="certificate-description">{{ $certificat->titre_certification }}</p>
        </div>
        
        <!-- Date - Bottom Left -->
        <div class="date-section">
            <p class="date-value">{{ $certificat->date_obtention->format('F j, Y') }}</p>
            <p class="date-label">Date</p>
        </div>
        
        <!-- Signature - Bottom Right -->
        <div class="signature-section">
            <div class="signature-image">{{ $formateur->prenom }} {{ $formateur->nom }}</div>
            <p class="signature-name">{{ $formateur->prenom }} {{ $formateur->nom }}</p>
            <p class="signature-title">Formateur Certifié, {{ $platform_name }}</p>
            
            <div class="certificate-details">
                Date d'émission: {{ $date_generation->format('d/m/Y') }}<br>
                Référence: #{{ str_pad($certificat->id, 6, '0', STR_PAD_LEFT) }}<br>
                Code: {{ strtoupper(substr(md5($certificat->id . $certificat->created_at), 0, 8)) }}
            </div>
        </div>
    </div>
</body>
</html>

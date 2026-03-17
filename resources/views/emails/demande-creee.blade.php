<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle demande de travaux</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background-color: #2B1444; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
            <h1 style="color: #fff; margin: 0; font-size: 24px;">SENTRAVAUX</h1>
            <p style="color: #FFD100; margin: 5px 0 0;">Gestion des Travaux</p>
        </div>
        
        <div style="background-color: #f9f9f9; padding: 30px; border: 1px solid #e0e0e0;">
            <h2 style="color: #2B1444; margin-top: 0;">Nouvelle demande de travaux</h2>
            
            <p>Une nouvelle demande de travaux a été créée :</p>
            
            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #e0e0e0; font-weight: bold; width: 40%;">N° Demande</td>
                    <td style="padding: 8px; border-bottom: 1px solid #e0e0e0;">{{ $demande->numero_demande }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #e0e0e0; font-weight: bold;">Objet</td>
                    <td style="padding: 8px; border-bottom: 1px solid #e0e0e0;">{{ $demande->objet }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #e0e0e0; font-weight: bold;">Nature</td>
                    <td style="padding: 8px; border-bottom: 1px solid #e0e0e0;">{{ $demande->nature }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #e0e0e0; font-weight: bold;">Demandeur</td>
                    <td style="padding: 8px; border-bottom: 1px solid #e0e0e0;">{{ $demande->user?->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #e0e0e0; font-weight: bold;">Date</td>
                    <td style="padding: 8px; border-bottom: 1px solid #e0e0e0;">{{ $demande->created_at?->format('d/m/Y H:i') }}</td>
                </tr>
            </table>
            
            <p style="color: #666; font-size: 14px;">Merci de prendre les actions nécessaires.</p>
        </div>
        
        <div style="background-color: #2B1444; padding: 15px; text-align: center; border-radius: 0 0 8px 8px;">
            <p style="color: #fff; margin: 0; font-size: 12px;">SENELEC - Société Nationale d'Électricité du Sénégal</p>
        </div>
    </div>
</body>
</html>

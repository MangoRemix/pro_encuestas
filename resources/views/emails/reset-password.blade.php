<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña</title>
    <style>
        @media only screen and (max-width: 600px) {
            .container { padding: 15px !important; }
            .button { padding: 15px 20px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; font-family: sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 10px;">
        <tr>
            <td align="center">
                <!-- Wrapper table to control max-width -->
                <table class="container" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center">
                            <img src="{{ $message->embed(public_path('images/logoAlcaldia.png')) }}" alt="Logo" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; background: #fff; border: 2px solid #ddd; margin-bottom: 20px;">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <h2 style="color: #0B1E36; text-align: center; margin-top: 0;">Restablecer Contraseña</h2>
                            <p style="color: #333; font-size: 16px;">¡Hola, {{ $name }}!</p>
                            <p style="color: #333; font-size: 16px; line-height: 1.5;">Has recibido este correo electrónico porque se solicitó un restablecimiento de contraseña para tu cuenta en el Sistema de Encuestas. Si deseas continuar, haz clic en el botón de abajo:</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 20px 0;">
                            <a href="{{ $url }}" class="button" style="background-color: #0B1E36; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Restablecer Contraseña</a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="color: #777; font-size: 14px; line-height: 1.5;">Este enlace de restablecimiento caducará en 60 minutos. Si no solicitaste este restablecimiento, no se requiere ninguna otra acción.</p>
                            <p style="color: #777; font-size: 14px;">Atentamente,<br>Sistema de Encuestas</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

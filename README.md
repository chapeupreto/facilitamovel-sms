# Facilita Móvel SMS

API para envio de mensagens SMS usando a solução da empresa FacilitaMóvel (http://www.facilitamovel.com.br/)


# Utilização

```php
<?php

require_once 'facilitamovelsms.php';

$username = 'your_username';
$password = 'your_password';

$destinatario = '64981251142'; // telefone do destinatario incluindo o DDD
$mensagem = 'Mensagem a ser enviada para o destinatario';

$facilita = new FacilitaMovelSms($username, $password);
$facilita->send_sms($destinatario, $mensagem);
```

# Licença

Essa API é open-source e utiliza a licença [MIT](http://opensource.org/licenses/MIT "MIT")

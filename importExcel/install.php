<?php
require_once(__DIR__ . '/settings.php');
require_once (__DIR__.'/../crest.php');

$result = CRest::installApp();
if($result['rest_only'] === false):?>
<head>
	<script src="//api.bitrix24.com/api/v1/"></script>
	<?if($result['install'] == true):?>
	<script>
		BX24.init(function(){
		    // BX24.callUnbind('onCrmProductAdd', 'https://advina.ru/bitrix24/movens/handler.php');
            // BX24.callUnbind('onCrmProductUpdate', 'https://advina.ru/bitrix24/movens/handler.php');
            // BX24.callUnbind('onCrmProductAdd', 'https://api.movens.ru/app1/handler.php');
            // BX24.callUnbind('onCrmProductUpdate', 'https://api.movens.ru/app1/handler.php');
            // BX24.callBind('onCrmProductAdd', 'https://api.movens.ru/app1/handler.php');
            // BX24.callBind('onCrmProductUpdate', 'https://api.movens.ru/app1/handler.php');
            BX24.installFinish();
		});
	</script>
	<?endif;?>
</head>
<body>
	<?if($result['install'] == true):?>
		installation has been finished
	<?else:?>
		installation error
	<?endif;?>
</body>
<?endif;


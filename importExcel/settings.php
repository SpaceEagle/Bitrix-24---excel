<?
define('C_REST_CLIENT_ID','local.Application ID');//Application ID
define('C_REST_CLIENT_SECRET','Application key');//Application key
// or
// define('C_REST_WEB_HOOK_URL','https://movens.bitrix24.ru/rest/83/e43ozbpn8zy40hco/');//url on creat Webhook

//define('C_REST_CURRENT_ENCODING','windows-1251');
//define('C_REST_IGNORE_SSL',true);//turn off validate ssl by curl
define('C_REST_LOG_TYPE_DUMP',true); //logs save var_export for viewing convenience
define('C_REST_BLOCK_LOG',false);//turn off default logs
define('C_REST_LOGS_DIR', __DIR__ .'/logs/'); //directory path to save the log
define('C_SETTINGS_FILE', 'importExcel/settings.json');

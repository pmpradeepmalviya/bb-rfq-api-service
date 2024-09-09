<?php

return [
    'AWS_ACCESS_KEY_ID' => env('AWS_ACCESS_KEY_ID'),
    'AWS_SECRET_ACCESS_KEY' => env('AWS_SECRET_ACCESS_KEY'),
    'AWS_DEFAULT_REGION' => env('AWS_DEFAULT_REGION'),
    'AWS_USE_PATH_STYLE_ENDPOINT' => env('AWS_USE_PATH_STYLE_ENDPOINT'),

    // 'CLOUDWATCH_LOG_NAME' => 'bb-api-logs-live',
    // 'CLOUDWATCH_LOG_GROUP_NAME' => 'bb-api-logs-live',
    // 'CLOUDWATCH_LOG_REGION' => 'ap-south-1',
    // 'CLOUDWATCH_LOG_STREAM_NAME' => 'ipo-logs',
    // 'CLOUDWATCH_LOG_RETENTION_DAYS' => 30,
    // 'CLOUDWATCH_LOG_LEVEL' => 'info',
    // 'APP_LOG_LEVEL' => 'debug',

    // 'REDIS_HOST' => 'bb-trade-cache-data-prod.hjguf3.ng.0001.aps1.cache.amazonaws.com',
    // 'REDIS_PASSWORD' => null,
    // 'REDIS_PORT' => 6379,
    // 'REDIS_CLIENT' => 'predis',
    // 'REDIS_CLUSTER' => 'redis',
    // 'REDIS_EXPIRE_TIME_IN_SECONDS' => 1260, #21 minutes

    'LOG_REQUIRED' => true,
    'TOKEN_RFQ_API_SERVICE' => 'xXXpz??rWS?Wv?Q?MAx1oZY!S/0qS-5MUWYtCpYHytoi6tkn80DU9CEx9SkDVZcTKw?w62W=wG!75wDMx?KbfisrOyinwTFVZifB-Y??N0h2JUTNxpwHD6cA32U8?Pj8plM!E1eIE!Uerb!ZOKpShe?oUNh-reyoLUjkjfUD2eNR!1a2Gwa?6dVW2LeqSgoo762RMhXDFuYHNSPWBXowjlCVYe1vbingew68PhEUiwAb-yF8KI?UhVyhfdm9jgrs',
    'FILE_LOG_REQUIRED' => true,
    'DB_LOG_REQUIRED' => true,
    'REQUEST_MESSAGE' => true,
    'RESPONSE_MESSAGE' => true,
    'PROD_SHLIPIURL' => "http://49.248.155.148:8002/?DEALBOOK=",
    'SHLIPIURL'=>"", // as we dont want to hit the api as of now
    'notificationurl'=>'https://uat.bondbazaar.com/alerts/api/',
    'BSEValidateCheckSum'=> 'https://appdemo.bseindia.com/ICDMAPI/ICDMService.svc/ValidateCheckSum'
];

?>

<?php

const PRIVATE_DIR = '/home/heidrun/private';
const WALLET_DIR = PRIVATE_DIR . '/wallets';

const WALLET_TYPE_PAYMENT = 'Payment';
const WALLET_TYPE_DROP = 'Drop';

const NETWORK_MAINNET = 'mainnet';
const NETWORK_TESTNET = 'testnet';

const JOB_TYPE_TRACK_PAYMENT_AND_CALLBACK = 'TrackPaymentAndCallback';
const JOB_TYPE_TRACK_PAYMENT_AND_DROP_ASSET = 'TrackPaymentAndDropAsset';

const JOB_STATUS_PENDING = 'Pending';
const JOB_STATUS_PROCESSING = 'Processing';
const JOB_STATUS_SUCCESS = 'Success';
const JOB_STATUS_ERROR = 'Error';

const JOB_MAX_ATTEMPTS = 10;
const JOB_RETRY_INTERVAL_SECONDS = 60;

const CALLBACK_REQUEST_TYPE_GET = 'get';
const CALLBACK_REQUEST_TYPE_POST = 'post';

const DROP_ASSET_TX_FEE_IN_LOVELACE = 200000;

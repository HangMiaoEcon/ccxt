<?php

namespace ccxt;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

use Exception as Exception; // a common import

class dsx extends liqui {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'dsx',
            'name' => 'DSX',
            'countries' => 'UK',
            'rateLimit' => 1500,
            'has' => array (
                'CORS' => false,
                'fetchOrder' => true,
                'fetchOrders' => true,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'fetchTickers' => true,
                'fetchMyTrades' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27990275-1413158a-645a-11e7-931c-94717f7510e3.jpg',
                'api' => array (
                    'public' => 'https://dsx.uk/mapi', // market data
                    'private' => 'https://dsx.uk/tapi', // trading
                    'dwapi' => 'https://dsx.uk/dwapi', // deposit/withdraw
                ),
                'www' => 'https://dsx.uk',
                'doc' => array (
                    'https://api.dsx.uk',
                    'https://dsx.uk/api_docs/public',
                    'https://dsx.uk/api_docs/private',
                    '',
                ),
            ),
            'api' => array (
                // market data (public)
                'public' => array (
                    'get' => array (
                        'barsFromMoment/{id}/{period}/{start}', // empty reply :\
                        'depth/{pair}',
                        'info',
                        'lastBars/{id}/{period}/{amount}', // period is (m, h or d)
                        'periodBars/{id}/{period}/{start}/{end}',
                        'ticker/{pair}',
                        'trades/{pair}',
                    ),
                ),
                // trading (private)
                'private' => array (
                    'post' => array (
                        'getInfo',
                        'TransHistory',
                        'TradeHistory',
                        'OrderHistory',
                        'ActiveOrders',
                        'Trade',
                        'CancelOrder',
                    ),
                ),
                // deposit / withdraw (private)
                'dwapi' => array (
                    'post' => array (
                        'getCryptoDepositAddress',
                        'cryptoWithdraw',
                        'fiatWithdraw',
                        'getTransactionStatus',
                        'getTransactions',
                    ),
                ),
            ),
        ));
    }

    public function get_base_quote_from_market_id ($id) {
        $uppercase = strtoupper ($id);
        $base = mb_substr ($uppercase, 0, 3);
        $quote = mb_substr ($uppercase, 3, 6);
        $base = $this->common_currency_code($base);
        $quote = $this->common_currency_code($quote);
        return array ( $base, $quote );
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostGetInfo ();
        $balances = $response['return'];
        $result = array ( 'info' => $balances );
        $funds = $balances['funds'];
        $currencies = is_array ($funds) ? array_keys ($funds) : array ();
        for ($c = 0; $c < count ($currencies); $c++) {
            $currency = $currencies[$c];
            $uppercase = strtoupper ($currency);
            $uppercase = $this->common_currency_code($uppercase);
            $account = array (
                'free' => $funds[$currency],
                'used' => 0.0,
                'total' => $balances['total'][$currency],
            );
            $account['used'] = $account['total'] - $account['free'];
            $result[$uppercase] = $account;
        }
        return $this->parse_balance($result);
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $ticker['updated'] * 1000;
        $symbol = null;
        if ($market)
            $symbol = $market['symbol'];
        $average = $this->safe_float($ticker, 'avg');
        if ($average !== null)
            if ($average > 0)
                $average = 1 / $average;
        $last = $this->safe_float($ticker, 'last');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => $this->safe_float($ticker, 'buy'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'sell'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => $average,
            'baseVolume' => $this->safe_float($ticker, 'vol'),
            'quoteVolume' => $this->safe_float($ticker, 'vol_cur'),
            'info' => $ticker,
        );
    }

    public function get_order_id_key () {
        return 'orderId';
    }

    public function sign_body_with_secret ($body) {
        return $this->decode ($this->hmac ($this->encode ($body), $this->encode ($this->secret), 'sha512', 'base64'));
    }

    public function get_version_string () {
        return ''; // they don't prepend version number to public URLs as other BTC-e clones do
    }
}

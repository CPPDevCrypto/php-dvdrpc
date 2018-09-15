<?php

use Cppdevcrypto\Dividend;
use Cppdevcrypto\Dividend\Exceptions;
use GuzzleHttp\Psr7\Response;

class ClientTest extends TestCase
{
    /**
     * Set up test.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->Dividendd = new Dividend\Client();
    }

    /**
     * Test url parser.
     *
     * @param string $url
     * @param string $scheme
     * @param string $host
     * @param int    $port
     * @param string $user
     * @param string $pass
     *
     * @return void
     *
     * @dataProvider urlProvider
     */
    public function testUrlParser($url, $scheme, $host, $port, $user, $pass)
    {
        $Dividendd = new Dividend\Client($url);

        $this->assertInstanceOf(Dividend\Client::class, $Dividendd);

        $base_uri = $Dividendd->getConfig('base_uri');

        $this->assertEquals($base_uri->getScheme(), $scheme);
        $this->assertEquals($base_uri->getHost(), $host);
        $this->assertEquals($base_uri->getPort(), $port);

        $auth = $Dividendd->getConfig('auth');
        $this->assertEquals($auth[0], $user);
        $this->assertEquals($auth[1], $pass);
    }

    /**
     * Data provider for url expander test.
     *
     * @return array
     */
    public function urlProvider()
    {
        return [
            ['https://localhost', 'https', 'localhost', 8332, '', ''],
            ['https://localhost:8000', 'https', 'localhost', 8000, '', ''],
            ['http://localhost', 'http', 'localhost', 8332, '', ''],
            ['http://localhost:8000', 'http', 'localhost', 8000, '', ''],
            ['http://testuser@127.0.0.1:8000/', 'http', '127.0.0.1', 8000, 'testuser', ''],
            ['http://testuser:testpass@localhost:8000', 'http', 'localhost', 8000, 'testuser', 'testpass'],
        ];
    }

    /**
     * Test url parser with invalid url.
     *
     * @return array
     */
    public function testUrlParserWithInvalidUrl()
    {
        try {
            $Dividendd = new Dividend\Client('cookies!');

            $this->expectException(Exceptions\ClientException::class);
        } catch (Exceptions\ClientException $e) {
            $this->assertEquals('Invalid url', $e->getMessage());
        }
    }

    /**
     * Test client getter and setter.
     *
     * @return void
     */
    public function testClientSetterGetter()
    {
        $Dividendd = new Dividend\Client('http://old_client.org');
        $this->assertInstanceOf(Dividend\Client::class, $Dividendd);

        $base_uri = $Dividendd->getConfig('base_uri');
        $this->assertEquals($base_uri->getHost(), 'old_client.org');

        $oldClient = $Dividendd->getClient();
        $this->assertInstanceOf(\GuzzleHttp\Client::class, $oldClient);

        $newClient = new \GuzzleHttp\Client(['base_uri' => 'http://new_client.org']);
        $Dividendd->setClient($newClient);

        $base_uri = $Dividendd->getConfig('base_uri');
        $this->assertEquals($base_uri->getHost(), 'new_client.org');
    }

    /**
     * Test ca config option.
     *
     * @return void
     */
    public function testCaOption()
    {
        $Dividendd = new Dividend\Client();

        $this->assertEquals(null, $Dividendd->getConfig('ca'));

        $Dividendd = new Dividend\Client([
            'ca' => __FILE__,
        ]);

        $this->assertEquals(__FILE__, $Dividendd->getConfig('verify'));
    }

    /**
     * Test simple request.
     *
     * @return void
     */
    public function testRequest()
    {
        $guzzle = $this->mockGuzzle([
            $this->getBlockResponse(),
        ]);

        $response = $this->Dividendd
            ->setClient($guzzle)
            ->request(
                'getblockheader',
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
            );

        $request = $this->getHistoryRequestBody();

        $this->assertEquals($this->makeRequestBody(
            'getblockheader',
            $request['id'],
            '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
        ), $request);
        $this->assertEquals(self::$getBlockResponse, $response->get());
    }

    /**
     * Test multiwallet request.
     *
     * @return void
     */
    public function testMultiWalletRequest()
    {
        $wallet = 'testwallet.dat';

        $guzzle = $this->mockGuzzle([
            $this->getBalanceResponse(),
        ]);

        $response = $this->Dividendd
            ->setClient($guzzle)
            ->wallet($wallet)
            ->request('getbalance');

        $this->assertEquals(self::$balanceResponse, $response->get());
        $this->assertEquals(
            $this->getHistoryRequestUri()->getPath(),
            "/wallet/$wallet"
        );
    }

    /**
     * Test async multiwallet request.
     *
     * @return void
     */
    public function testMultiWalletAsyncRequest()
    {
        $wallet = 'testwallet2.dat';

        $guzzle = $this->mockGuzzle([
            $this->getBalanceResponse(),
        ]);

        $this->Dividendd
            ->setClient($guzzle)
            ->wallet($wallet)
            ->requestAsync('getbalance', []);

        $this->Dividendd->__destruct();

        $this->assertEquals(
            $this->getHistoryRequestUri()->getPath(),
            "/wallet/$wallet"
        );
    }

    /**
     * Test async request.
     *
     * @return void
     */
    public function testAsyncRequest()
    {
        $guzzle = $this->mockGuzzle([
            $this->getBlockResponse(),
        ]);

        $onFulfilled = $this->mockCallable([
            $this->callback(function (Dividend\DividenddResponse $response) {
                return $response->get() == self::$getBlockResponse;
            }),
        ]);

        $this->Dividendd
            ->setClient($guzzle)
            ->requestAsync(
                'getblockheader',
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
                function ($response) use ($onFulfilled) {
                    $onFulfilled($response);
                }
            );

        $this->Dividendd->__destruct();

        $request = $this->getHistoryRequestBody();
        $this->assertEquals($this->makeRequestBody(
            'getblockheader',
            $request['id'],
            '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
        ), $request);
    }

    /**
     * Test magic request.
     *
     * @return void
     */
    public function testMagic()
    {
        $guzzle = $this->mockGuzzle([
            $this->getBlockResponse(),
        ]);

        $response = $this->Dividendd
            ->setClient($guzzle)
            ->getBlockHeader(
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
            );

        $request = $this->getHistoryRequestBody();
        $this->assertEquals($this->makeRequestBody(
            'getblockheader',
            $request['id'],
            '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
        ), $request);
    }

    /**
     * Test magic request.
     *
     * @return void
     */
    public function testAsyncMagic()
    {
        $guzzle = $this->mockGuzzle([
            $this->getBlockResponse(),
        ]);

        $onFulfilled = $this->mockCallable([
            $this->callback(function (Dividend\DividenddResponse $response) {
                return $response->get() == self::$getBlockResponse;
            }),
        ]);

        $this->Dividendd
            ->setClient($guzzle)
            ->getBlockHeaderAsync(
                '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
                function ($response) use ($onFulfilled) {
                    $onFulfilled($response);
                }
            );

        $this->Dividendd->__destruct();

        $request = $this->getHistoryRequestBody();
        $this->assertEquals($this->makeRequestBody(
            'getblockheader',
            $request['id'],
            '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f'
        ), $request);
    }

    /**
     * Test Dividendd exception.
     *
     * @return void
     */
    public function testDividenddException()
    {
        $guzzle = $this->mockGuzzle([
            $this->rawTransactionError(200),
        ]);

        $this->expectException(Exceptions\DividenddException::class);
        $this->expectExceptionMessage(self::$rawTransactionError['message']);
        $this->expectExceptionCode(self::$rawTransactionError['code']);

        $this->Dividendd
            ->setClient($guzzle)
            ->getRawTransaction(
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
            );
    }

    /**
     * Test async Dividendd exception.
     *
     * @return void
     */
    public function testAsyncDividenddException()
    {
        $guzzle = $this->mockGuzzle([
            $this->rawTransactionError(200),
        ]);

        $onFulfilled = $this->mockCallable([
            $this->callback(function (Exceptions\DividenddException $exception) {
                return $exception->getMessage() == self::$rawTransactionError['message'] &&
                    $exception->getCode() == self::$rawTransactionError['code'];
            }),
        ]);

        $this->Dividendd
            ->setClient($guzzle)
            ->requestAsync(
                'getrawtransaction',
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
                function ($response) use ($onFulfilled) {
                    $onFulfilled($response);
                }
            );

        $this->Dividendd->__destruct();
    }

    /**
     * Test request exception with error code.
     *
     * @return void
     */
    public function testRequestExceptionWithServerErrorCode()
    {
        $guzzle = $this->mockGuzzle([
            $this->rawTransactionError(500),
        ]);

        $this->expectException(Exceptions\DividenddException::class);
        $this->expectExceptionMessage(self::$rawTransactionError['message']);
        $this->expectExceptionCode(self::$rawTransactionError['code']);

        $this->Dividendd
            ->setClient($guzzle)
            ->getRawTransaction(
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
            );
    }

    /**
     * Test request exception with empty response body.
     *
     * @return void
     */
    public function testRequestExceptionWithEmptyResponseBody()
    {
        $guzzle = $this->mockGuzzle([
            new Response(500),
        ]);

        $this->expectException(Exceptions\ClientException::class);
        $this->expectExceptionMessage($this->error500());
        $this->expectExceptionCode(500);

        $this->Dividendd
            ->setClient($guzzle)
            ->getRawTransaction(
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
            );
    }

    /**
     * Test async request exception with empty response body.
     *
     * @return void
     */
    public function testAsyncRequestExceptionWithEmptyResponseBody()
    {
        $guzzle = $this->mockGuzzle([
            new Response(500),
        ]);

        $rejected = $this->mockCallable([
            $this->callback(function (Exceptions\ClientException $exception) {
                return $exception->getMessage() == $this->error500() &&
                    $exception->getCode() == 500;
            }),
        ]);

        $this->Dividendd
            ->setClient($guzzle)
            ->requestAsync(
                'getrawtransaction',
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
                null,
                function ($exception) use ($rejected) {
                    $rejected($exception);
                }
            );

        $this->Dividendd->__destruct();
    }

    /**
     * Test request exception with response.
     *
     * @return void
     */
    public function testRequestExceptionWithResponseBody()
    {
        $guzzle = $this->mockGuzzle([
            $this->requestExceptionWithResponse(),
        ]);

        $this->expectException(Exceptions\DividenddException::class);
        $this->expectExceptionMessage(self::$rawTransactionError['message']);
        $this->expectExceptionCode(self::$rawTransactionError['code']);

        $this->Dividendd
            ->setClient($guzzle)
            ->getRawTransaction(
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
            );
    }

    /**
     * Test async request exception with response.
     *
     * @return void
     */
    public function testAsyncRequestExceptionWithResponseBody()
    {
        $guzzle = $this->mockGuzzle([
            $this->requestExceptionWithResponse(),
        ]);

        $onRejected = $this->mockCallable([
            $this->callback(function (Exceptions\DividenddException $exception) {
                return $exception->getMessage() == self::$rawTransactionError['message'] &&
                    $exception->getCode() == self::$rawTransactionError['code'];
            }),
        ]);

        $this->Dividendd
            ->setClient($guzzle)
            ->requestAsync(
                'getrawtransaction',
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
                null,
                function ($exception) use ($onRejected) {
                    $onRejected($exception);
                }
            );

        $this->Dividendd->__destruct();
    }

    /**
     * Test request exception with no response.
     *
     * @return void
     */
    public function testRequestExceptionWithNoResponseBody()
    {
        $guzzle = $this->mockGuzzle([
            $this->requestExceptionWithoutResponse(),
        ]);

        $this->expectException(Exceptions\ClientException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $this->Dividendd
            ->setClient($guzzle)
            ->getRawTransaction(
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b'
            );
    }

    /**
     * Test async request exception with no response.
     *
     * @return void
     */
    public function testAsyncRequestExceptionWithNoResponseBody()
    {
        $guzzle = $this->mockGuzzle([
            $this->requestExceptionWithoutResponse(),
        ]);

        $rejected = $this->mockCallable([
            $this->callback(function (Exceptions\ClientException $exception) {
                return $exception->getMessage() == 'test' &&
                    $exception->getCode() == 0;
            }),
        ]);

        $this->Dividendd
            ->setClient($guzzle)
            ->requestAsync(
                'getrawtransaction',
                '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
                null,
                function ($exception) use ($rejected) {
                    $rejected($exception);
                }
            );

        $this->Dividendd->__destruct();
    }
}

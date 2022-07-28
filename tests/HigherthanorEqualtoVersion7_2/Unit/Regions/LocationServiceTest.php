<?php

namespace AlibabaCloud\Client\Tests\HigherthanorEqualtoVersion7_2\Unit\Regions;

use PHPUnit\Framework\TestCase;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Regions\LocationService;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Client\Tests\Mock\Services\Rds\DeleteDatabaseRequest;

/**
 * Class LocationServiceTest
 *
 * @package   AlibabaCloud\Client\Tests\HigherthanorEqualtoVersion7_2\Unit\Endpoint
 */
class LocationServiceTest extends TestCase
{

    /**
     * @throws ClientException
     * @throws ServerException
     */
    public function testAddHost()
    {
        // Setup
        $product  = 'b';
        $host     = 'c';
        $regionId = 'a';

        // Test
        $request = AlibabaCloud::rpc()->regionId($regionId)->product($product);
        LocationService::addHost($product, $host, $regionId);

        // Assert
        self::assertEquals(LocationService::resolveHost($request), $host);
    }

    /**
     * @throws ClientException
     * @throws ServerException
     */
    public function testResolveHostWithServiceUnknownError()
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessageMatches("/Not found Region ID in location.aliyuncs.com/");
        AlibabaCloud::mockResponse();
        $request = AlibabaCloud::rpc()->product(__METHOD__)
                               ->regionId('regionId');

        $host = LocationService::resolveHost($request);
        self::assertEquals('', $host);
    }

    /**
     * @throws ClientException
     * @throws ServerException
     */
    public function testResolveHostNotFound()
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage("Not found Region ID in location.aliyuncs.com");
        AlibabaCloud::mockResponse();

        $request = AlibabaCloud::rpc()->product(__METHOD__)->regionId('regionId');

        LocationService::resolveHost($request);
    }

    /**
     * @throws ClientException
     * @throws ServerException
     */
    public function testResolveHostSuccess()
    {
        $body = [
            'Endpoints' => [
                'Endpoint' => [
                    0 => [
                        'Endpoint' => 'cdn.aliyun.com',
                    ],
                ],
            ],
        ];

        AlibabaCloud::mockResponse(200, [], $body);

        $request = AlibabaCloud::rpc()->product(__METHOD__)->regionId('regionId');

        $host = LocationService::resolveHost($request);

        self::assertEquals('cdn.aliyun.com', $host);
    }

    /**
     * @throws ClientException
     */
    public function testAddHostWithProductEmpty()
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage("Product cannot be empty");
        LocationService::addHost('', 'host', 'regionId');
    }

    /**
     * @throws ClientException
     */
    public function testAddHostWithProductFormat()
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage("Product must be a string");
        LocationService::addHost(null, 'host', 'regionId');
    }

    /**
     * @throws ClientException
     */
    public function testAddHostWithHostEmpty()
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage("Host cannot be empty");
        LocationService::addHost('product', '', 'regionId');
    }

    /**
     * @throws ClientException
     */
    public function testAddHostWithHostFormat()
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage("Host must be a string");
        LocationService::addHost('product', null, 'regionId');
    }

    /**
     * @throws ClientException
     */
    public function testAddHostWithRegionIdEmpty()
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage("Region ID cannot be empty");
        LocationService::addHost('product', 'host', '');
    }

    /**
     * @throws ClientException
     */
    public function testAddHostWithRegionIdFormat()
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage("Region ID must be a string");
        LocationService::addHost('product', 'host', null);
    }

    /**
     * @throws ClientException
     * @throws ServerException
     */
    public function testLocationServiceException()
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessageMatches("/Specified access key is not found/");
        // Setup
        AlibabaCloud::accessKeyClient('key', 'secret')->asDefaultClient();

        AlibabaCloud::mockResponse(
            401,
            [],
            [
                'message' => 'Specified access key is not found',
            ]
        );

        $request = (new DeleteDatabaseRequest())
            ->regionId('cn-hangzhou')
            ->connectTimeout(25)
            ->timeout(30);

        // Test
        LocationService::resolveHost($request);
    }

    /**
     * @throws ClientException
     * @throws ServerException
     */
    public function testLocationServiceWithBadServiceDomain()
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessageMatches("/cURL error 6: Could not resolve/");
        AlibabaCloud::accessKeyClient('key', 'secret')->asDefaultClient();
        $request = (new DeleteDatabaseRequest())->regionId('cn-hangzhou');
        LocationService::resolveHost($request, 'not.alibaba.com');
    }

    /**
     * @throws ClientException
     */
    protected function setUp(): void
    {
        AlibabaCloud::accessKeyClient('foo', 'bar')->asDefaultClient();
    }

    protected function tearDown(): void
    {
        AlibabaCloud::cancelMock();
    }
}

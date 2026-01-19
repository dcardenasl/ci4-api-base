<?php

namespace Tests\Unit;

use dcardenasl\CI4ApiBase\Controllers\ApiController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockIncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\Services;
use InvalidArgumentException;
use RuntimeException;
use ReflectionProperty;

class ApiControllerTest extends CIUnitTestCase
{
    protected ApiController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Concrete test implementation
        $this->controller = new class extends ApiController {
            private object $service;

            public function setService(object $service): void
            {
                $this->service = $service;
            }

            protected function getService(): object
            {
                return $this->service ?? new class {
                    public function testMethod(array $data): array
                    {
                        return ['success' => true, 'data' => $data];
                    }

                    public function throwException(array $data): array
                    {
                        throw new InvalidArgumentException('Test exception');
                    }
                };
            }

            protected function getSuccessStatus(string $method): int
            {
                return ResponseInterface::HTTP_OK;
            }

            // Expose protected methods
            public function publicCollectRequestData(?array $item = null): array
            {
                return $this->collectRequestData($item);
            }

            public function publicGetJsonData(): array
            {
                return $this->getJsonData();
            }

            public function publicDetermineStatus(array $result, string $method): int
            {
                return $this->determineStatus($result, $method);
            }

            public function publicHandleException(\Exception $e): ResponseInterface
            {
                return $this->handleException($e);
            }

            // Expose protected response methods
            public function respondCreated(array $data = []): ResponseInterface
            {
                return parent::respondCreated($data);
            }

            public function respondNoContent(): ResponseInterface
            {
                return parent::respondNoContent();
            }

            public function respondNotFound(string $message = 'Resource not found'): ResponseInterface
            {
                return parent::respondNotFound($message);
            }

            public function respondUnauthorized(string $message = 'Unauthorized'): ResponseInterface
            {
                return parent::respondUnauthorized($message);
            }

            public function respondValidationError(array $errors): ResponseInterface
            {
                return parent::respondValidationError($errors);
            }
        };

        // 🔐 Inject protected properties via reflection
        $this->injectProperty($this->controller, 'response', Services::response());
    }

    protected function injectProperty(object $object, string $property, mixed $value): void
    {
        $ref = new ReflectionProperty($object, $property);
        $ref->setAccessible(true);
        $ref->setValue($object, $value);
    }

    public function testCollectRequestDataMergesAllSources(): void
    {
        $request = new MockIncomingRequest(
            new \Config\App(),
            new URI('http://example.com/api/test?param1=value1'),
            null,
            new UserAgent(),
            'GET'
        );

        $this->injectProperty($this->controller, 'request', $request);

        $itemData = ['id' => 123];
        $result = $this->controller->publicCollectRequestData($itemData);

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals(123, $result['id']);
    }

    public function testGetJsonDataParsesValidJson(): void
    {
        $jsonData = json_encode(['name' => 'Test Product', 'price' => 99.99]);

        $request = new MockIncomingRequest(
            new \Config\App(),
            new URI('http://example.com/api/test'),
            $jsonData,
            new UserAgent(),
            'POST'
        );

        $this->injectProperty($this->controller, 'request', $request);

        $result = $this->controller->publicGetJsonData();

        $this->assertIsArray($result);
        $this->assertEquals('Test Product', $result['name']);
        $this->assertEquals(99.99, $result['price']);
    }

    public function testGetJsonDataReturnsEmptyArrayForInvalidJson(): void
    {
        $request = new MockIncomingRequest(
            new \Config\App(),
            new URI('http://example.com/api/test'),
            'invalid json{',
            new UserAgent(),
            'POST'
        );

        $this->injectProperty($this->controller, 'request', $request);

        $result = $this->controller->publicGetJsonData();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetJsonDataReturnsEmptyArrayForEmptyBody(): void
    {
        $request = new MockIncomingRequest(
            new \Config\App(),
            new URI('http://example.com/api/test'),
            '',
            new UserAgent(),
            'POST'
        );

        $this->injectProperty($this->controller, 'request', $request);

        $result = $this->controller->publicGetJsonData();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testDetermineStatusReturnsSuccessStatusWhenNoErrors(): void
    {
        $result = ['data' => ['id' => 1]];
        $status = $this->controller->publicDetermineStatus($result, 'testMethod');

        $this->assertEquals(ResponseInterface::HTTP_OK, $status);
    }

    public function testDetermineStatusReturnsBadRequestWhenErrorsExist(): void
    {
        $result = ['errors' => ['name' => 'Name is required']];
        $status = $this->controller->publicDetermineStatus($result, 'testMethod');

        $this->assertEquals(ResponseInterface::HTTP_BAD_REQUEST, $status);
    }

    public function testHandleExceptionReturnsBadRequestForInvalidArgumentException(): void
    {
        $exception = new InvalidArgumentException('Invalid input');
        $response  = $this->controller->publicHandleException($exception);

        $this->assertEquals(ResponseInterface::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals('Invalid input', json_decode($response->getBody(), true)['error']);
    }

    public function testHandleExceptionReturns500ForRuntimeException(): void
    {
        $exception = new RuntimeException('Server error');
        $response  = $this->controller->publicHandleException($exception);

        $this->assertEquals(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals('Server error', json_decode($response->getBody(), true)['error']);
    }

    public function testRespondCreatedReturns201Status(): void
    {
        $data = ['id' => 1, 'name' => 'New Product'];
        $response = $this->controller->respondCreated($data);

        $this->assertEquals(ResponseInterface::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals($data, json_decode($response->getBody(), true));
    }

    public function testRespondNoContentReturns204Status(): void
    {
        $response = $this->controller->respondNoContent();
        $this->assertEquals(ResponseInterface::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testRespondNotFoundReturns404Status(): void
    {
        $message = 'Product not found';
        $response = $this->controller->respondNotFound($message);

        $this->assertEquals(ResponseInterface::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals($message, json_decode($response->getBody(), true)['error']);
    }

    public function testRespondUnauthorizedReturns401Status(): void
    {
        $response = $this->controller->respondUnauthorized();

        $this->assertEquals(ResponseInterface::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertEquals('Unauthorized', json_decode($response->getBody(), true)['error']);
    }

    public function testRespondValidationErrorReturns422Status(): void
    {
        $errors = ['name' => 'Name is required', 'price' => 'Price must be numeric'];
        $response = $this->controller->respondValidationError($errors);

        $this->assertEquals(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertEquals($errors, json_decode($response->getBody(), true)['errors']);
    }
}

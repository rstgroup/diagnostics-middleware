<?php

namespace RstGroup\DiagnosticsMiddleware\Test;

use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use RstGroup\DiagnosticsMiddleware\RunnerChecksFactory;
use ZendDiagnostics\Check\CheckInterface;

class RunnerChecksFactoryTest extends TestCase
{
    protected $factory;
    protected $container;
    protected $containerCall = [];

    protected function setUp()
    {
        $this->factory = new RunnerChecksFactory();
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testEmptyList()
    {
        $this->mockChecksConfig([]);

        $result = $this->create();

        $this->assertSame([], $result);
    }

    public function testCheckList()
    {
        $this->mockChecksConfig([
            'group' => [
                'my-check' => 'service-name',
                'my-second-check' => 'other-service-name',
            ]
        ]);

        $check = $this->createMock(CheckInterface::class);

        $this->containerCall[] = ['service-name', $check];
        $this->containerCall[] = ['other-service-name', $check];

        $result = $this->create();

        $this->assertSame(['group/my-check' => $check, 'group/my-second-check' => $check], $result);
    }

    protected function create()
    {
        $this->container->method('get')->will($this->returnValueMap($this->containerCall));

        return call_user_func($this->factory, $this->container);;
    }

    protected function mockChecksConfig(array $checks)
    {
        $this->containerCall[] = ['config', [
                'rstgroup' => [
                    'diagnostics_middleware' => [
                        'checks' => $checks,
                    ],
                ],
            ],
        ];
    }
}

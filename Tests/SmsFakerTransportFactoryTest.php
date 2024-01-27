<?php

namespace BrunoJunior\Symfony\SmsFaker\Tests;

use BrunoJunior\Symfony\SmsFaker\SmsFakerTransportFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Test\TransportFactoryTestCase;
use Symfony\Component\Notifier\Transport\Dsn;

final class SmsFakerTransportFactoryTest extends TransportFactoryTestCase
{
    public static function createProvider(): iterable
    {
        yield [
            'smsfaker+email://default?to=recipient@email.net&from=sender@email.net',
            'smsfaker+email://default?to=recipient@email.net&from=sender@email.net',
        ];

        yield [
            'smsfaker+email://mailchimp?to=recipient@email.net&from=sender@email.net',
            'smsfaker+email://mailchimp?to=recipient@email.net&from=sender@email.net',
        ];

        yield [
            'smsfaker+logger://default',
            'smsfaker+logger://default',
        ];
    }

    public static function missingRequiredOptionProvider(): iterable
    {
        yield 'missing option: from' => ['smsfaker+email://default?to=recipient@email.net'];
        yield 'missing option: to' => ['smsfaker+email://default?from=sender@email.net'];
    }

    public static function supportsProvider(): iterable
    {
        yield [true, 'smsfaker+email://default?to=recipient@email.net&from=sender@email.net'];
        yield [false, 'somethingElse://default?to=recipient@email.net&from=sender@email.net'];
    }

    public static function incompleteDsnProvider(): iterable
    {
        yield 'missing from' => ['smsfaker+email://default?to=recipient@email.net'];
        yield 'missing to' => ['smsfaker+email://default?from=recipient@email.net'];
    }

    public static function unsupportedSchemeProvider(): iterable
    {
        yield ['somethingElse://default?to=recipient@email.net&from=sender@email.net'];
    }

    /**
     * @dataProvider missingRequiredDependencyProvider
     */
    public function testMissingRequiredDependency(
        ?MailerInterface $mailer,
        ?LoggerInterface $logger,
        string $dsn,
        string $message
    ) {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage($message);

        $factory = new SmsFakerTransportFactory($mailer, $logger);
        $factory->create(new Dsn($dsn));
    }

    /**
     * @dataProvider missingOptionalDependencyProvider
     */
    public function testMissingOptionalDependency(?MailerInterface $mailer, ?LoggerInterface $logger, string $dsn)
    {
        $factory = new SmsFakerTransportFactory($mailer, $logger);
        $transport = $factory->create(new Dsn($dsn));

        $this->assertSame($dsn, (string)$transport);
    }

    public function createFactory(): SmsFakerTransportFactory
    {
        return new SmsFakerTransportFactory(
            $this->createMock(MailerInterface::class),
            $this->createMock(LoggerInterface::class)
        );
    }

    public function missingRequiredDependencyProvider(): iterable
    {
        $exceptionMessage = 'Cannot create a transport for scheme "%s" without providing an implementation of "%s".';
        yield 'missing mailer' => [
            null,
            $this->createMock(LoggerInterface::class),
            'smsfaker+email://default?to=recipient@email.net&from=sender@email.net',
            sprintf($exceptionMessage, 'smsfaker+email', MailerInterface::class),
        ];
        yield 'missing logger' => [
            $this->createMock(MailerInterface::class),
            null,
            'smsfaker+logger://default',
            sprintf($exceptionMessage, 'smsfaker+logger', LoggerInterface::class),
        ];
    }

    public function missingOptionalDependencyProvider(): iterable
    {
        yield 'missing logger' => [
            $this->createMock(MailerInterface::class),
            null,
            'smsfaker+email://default?to=recipient@email.net&from=sender@email.net',
        ];
        yield 'missing mailer' => [
            null,
            $this->createMock(LoggerInterface::class),
            'smsfaker+logger://default',
        ];
    }
}
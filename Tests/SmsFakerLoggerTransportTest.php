<?php

namespace BrunoJunior\Symfony\SmsFaker\Tests;

use BrunoJunior\Symfony\SmsFaker\SmsFakerLoggerTransport;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Notifier\Test\TransportTestCase;
use Symfony\Component\Notifier\Tests\Transport\DummyMessage;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SmsFakerLoggerTransportTest extends TransportTestCase
{
    public static function toStringProvider(): iterable
    {
        yield ['smsfaker+logger://default', self::createTransport()];
    }

    public static function createTransport(
        ?HttpClientInterface $client = null,
        ?LoggerInterface $logger = null
    ): SmsFakerLoggerTransport {
        $transport = (new SmsFakerLoggerTransport($logger ?? new NullLogger(), $client ?? new MockHttpClient()));

        return $transport;
    }

    public static function supportedMessagesProvider(): iterable
    {
        yield [new SmsMessage('0611223344', 'Hello!')];
        yield [new SmsMessage('+33611223344', 'Hello!')];
    }

    public static function unsupportedMessagesProvider(): iterable
    {
        yield [new ChatMessage('Hello!')];
        yield [new DummyMessage()];
    }

    public function testSendWithDefaultTransport()
    {
        $message = SmsMessage::fromNotification(
            (new Notification('Hello!'))->content($content = 'Test'),
            new Recipient('', $phone = '0611223344')
        );

        $logger = new TestLogger();

        $transport = self::createTransport(null, $logger);

        $transport->send($message);

        $logs = $logger->logs;
        $this->assertNotEmpty($logs);

        $log = $logs[0];
        $this->assertSame(sprintf('[SMS for %s] %s', $phone, $message->getSubject()), $log['message']);
        $this->assertSame('info', $log['level']);
        $log2 = $logs[1];
        $this->assertSame($content, $log2['message']);
        $this->assertSame('info', $log2['level']);
    }
}
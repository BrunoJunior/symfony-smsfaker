<?php

namespace BrunoJunior\Symfony\SmsFaker\Tests;

use BrunoJunior\Symfony\SmsFaker\SmsFakerEmailTransport;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Mime\Email;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Notifier\Test\TransportTestCase;
use Symfony\Component\Notifier\Tests\Mailer\DummyMailer;
use Symfony\Component\Notifier\Tests\Transport\DummyMessage;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SmsFakerEmailTransportTest extends TransportTestCase
{
    public static function toStringProvider(): iterable
    {
        yield ['smsfaker+email://default?to=recipient@email.net&from=sender@email.net', self::createTransport()];
        yield [
            'smsfaker+email://mailchimp?to=recipient@email.net&from=sender@email.net',
            self::createTransport(null, 'mailchimp')
        ];
    }

    public static function createTransport(
        ?HttpClientInterface $client = null,
        ?string $transportName = null
    ): SmsFakerEmailTransport {
        $transport = (new SmsFakerEmailTransport(
            new DummyMailer(),
            'recipient@email.net',
            'sender@email.net',
            $client ?? new MockHttpClient()
        ));

        if (null !== $transportName) {
            $transport->setHost($transportName);
        }

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
        $transportName = null;

        $message = SmsMessage::fromNotification(
            (new Notification('Hello!'))->content($content = 'Test'),
            new Recipient('', $phone = '0611223344')
        );

        $mailer = new DummyMailer();

        $transport = (new SmsFakerEmailTransport($mailer, $to = 'recipient@email.net', $from = 'sender@email.net'));
        $transport->setHost($transportName);

        $transport->send($message);

        /** @var Email $sentEmail */
        $sentEmail = $mailer->getSentEmail();
        $this->assertInstanceOf(Email::class, $sentEmail);
        $this->assertSame($to, $sentEmail->getTo()[0]->getEncodedAddress());
        $this->assertSame($from, $sentEmail->getFrom()[0]->getEncodedAddress());
        $this->assertSame(sprintf('[SMS for %s] %s', $phone, $message->getSubject()), $sentEmail->getSubject());
        $this->assertSame($content, $sentEmail->getTextBody());
        $this->assertFalse($sentEmail->getHeaders()->has('X-Transport'));
    }

    public function testSendWithCustomTransport()
    {
        $transportName = 'mailchimp';

        $message = SmsMessage::fromNotification(
            (new Notification('Hello!'))->content($content = 'Test'),
            new Recipient('', $phone = '0611223344')
        );

        $mailer = new DummyMailer();

        $transport = (new SmsFakerEmailTransport($mailer, $to = 'recipient@email.net', $from = 'sender@email.net'));
        $transport->setHost($transportName);

        $transport->send($message);

        /** @var Email $sentEmail */
        $sentEmail = $mailer->getSentEmail();
        $this->assertInstanceOf(Email::class, $sentEmail);
        $this->assertSame($to, $sentEmail->getTo()[0]->getEncodedAddress());
        $this->assertSame($from, $sentEmail->getFrom()[0]->getEncodedAddress());
        $this->assertSame(sprintf('[SMS for %s] %s', $phone, $message->getSubject()), $sentEmail->getSubject());
        $this->assertSame($content, $sentEmail->getTextBody());
        $this->assertTrue($sentEmail->getHeaders()->has('X-Transport'));
        $this->assertSame($transportName, $sentEmail->getHeaders()->get('X-Transport')->getBody());
    }
}
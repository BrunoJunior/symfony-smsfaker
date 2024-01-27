<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrunoJunior\Symfony\SmsFaker;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author James Hemery <james@yieldstudio.fr>
 * @author Oskar Stark <oskarstark@googlemail.com>
 * @author Antoine Makdessi <amakdessi@me.com>
 */
final class SmsFakerTransportFactory extends AbstractTransportFactory
{
    private ?MailerInterface $mailer;
    private ?LoggerInterface $logger;

    public function __construct(
        MailerInterface $mailer = null,
        LoggerInterface $logger = null,
        EventDispatcherInterface $dispatcher = null,
        HttpClientInterface $client = null
    ) {
        parent::__construct($dispatcher, $client);

        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function create(Dsn $dsn): SmsFakerEmailTransport|SmsFakerLoggerTransport
    {
        $scheme = $dsn->getScheme();

        if ('smsfaker+email' === $scheme) {
            if (null === $this->mailer) {
                $this->throwMissingDependencyException($scheme, MailerInterface::class, 'symfony/mailer');
            }

            $mailerTransport = $dsn->getHost();
            $to = $dsn->getRequiredOption('to');
            $from = $dsn->getRequiredOption('from');

            return (new SmsFakerEmailTransport($this->mailer, $to, $from, $this->client, $this->dispatcher))->setHost(
                $mailerTransport
            );
        }

        if ('smsfaker+logger' === $scheme) {
            if (null === $this->logger) {
                $this->throwMissingDependencyException($scheme, LoggerInterface::class, 'psr/log');
            }

            return new SmsFakerLoggerTransport($this->logger, $this->client, $this->dispatcher);
        }

        throw new UnsupportedSchemeException($dsn, 'smsfaker', $this->getSupportedSchemes());
    }

    private function throwMissingDependencyException(
        string $scheme,
        string $missingDependency,
        string $suggestedPackage
    ): void {
        throw new LogicException(
            sprintf(
                'Cannot create a transport for scheme "%s" without providing an implementation of "%s". Try running "composer require "%s"".',
                $scheme,
                $missingDependency,
                $suggestedPackage
            )
        );
    }

    protected function getSupportedSchemes(): array
    {
        return ['smsfaker+email', 'smsfaker+logger'];
    }
}

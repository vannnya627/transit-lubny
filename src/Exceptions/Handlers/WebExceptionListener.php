<?php

declare(strict_types=1);

namespace App\Exceptions\Handlers;

use App\Exceptions\Interfaces\ApiExceptionInterface;
use App\Exceptions\Resolvers\ExceptionMappingDTO;
use App\Exceptions\Resolvers\ExceptionMappingResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsEventListener(event: 'kernel.exception', priority: 20)]
final readonly class WebExceptionListener
{
    private const string FALLBACK_TYPE = 'https://datatracker.ietf.org/doc/html/rfc7231#section-6.6.1';

    public function __construct(
        private ExceptionMappingResolver $resolver,
        private LoggerInterface $logger,
        #[Autowire(param: 'kernel.debug')]
        private bool $isDebug,
        private Environment $twig,
    ) {}

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();

        $mapping = $this->resolver->resolve($exception::class);
        $mapping ??= ExceptionMappingDTO::fromTypeAndCode(self::FALLBACK_TYPE, Response::HTTP_INTERNAL_SERVER_ERROR);

        $statusCode = $mapping->code;
        $context = [];
        if ($exception instanceof ApiExceptionInterface) {
            $context = $exception->getContext();
        }

        if ($mapping->code >= Response::HTTP_INTERNAL_SERVER_ERROR || $mapping->loggable) {
            $this->logger->error($exception->getMessage(), [
                'url' => $event->getRequest()->getUri(),
                'context' => $context,
                'trace' => $this->isDebug ? $exception->getTraceAsString() : null,
            ]);
        }

        $template = match ($statusCode) {
            404 => 'error/404.html.twig',
            default => 'error/500.html.twig',
        };

        $html = $this->twig->render($template, [
            'statusCode' => $statusCode,
            'message' => $exception->getMessage(),
            'context' => $context,
        ]);

        $event->setResponse(new Response($html, $statusCode));

        $event->stopPropagation();
    }
}

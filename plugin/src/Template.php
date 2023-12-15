<?php

declare(strict_types=1);

namespace CrowdSec\Whm;

use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Twig\TemplateWrapper;
use Twig\TwigFunction;

/**
 * The template engine.
 *
 * @author    CrowdSec team
 *
 * @see      https://crowdsec.net CrowdSec Official Website
 *
 * @copyright Copyright (c) 2020+ CrowdSec
 * @license   MIT License
 */
class Template
{
    /** @var FormInterface|null */
    private $form;
    /** @var TemplateWrapper */
    private $template;

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \LogicException
     * @throws InvalidOptionsException
     */
    public function __construct(
        string $path,
        string $formTypeClass = '',
        array $formTypeData = [],
        string $templatesDir = Constants::TEMPLATES_DIR,
        array $options = []
    ) {
        $loader = new FilesystemLoader($templatesDir);
        //$options['debug'] = true;
        $twig = new Environment($loader, $options);
        //$twig->addExtension(new \Twig\Extension\DebugExtension());
        if (!empty($formTypeClass)) {
            $formFactory = Forms::createFormFactoryBuilder()->getFormFactory();
            $this->form = $formFactory->create($formTypeClass, $formTypeData);

            $twig->addExtension(new FormExtension());
            $formEngine = new TwigRendererEngine(['forms/bootstrap_4_layout.html.twig'], $twig);
            $twig->addRuntimeLoader(new FactoryRuntimeLoader([
                FormRenderer::class => function () use ($formEngine) {
                    return new FormRenderer($formEngine);
                },
            ]));
        }
        // @see https://symfony.com/doc/5.4/components/form.html#translation to implement translation
        // Required for bootstrap twig forms template
        $twig->addExtension(new TranslationExtension());

        // Add function to check if a string matches a shell wildcard pattern
        $twig->addFunction(new TwigFunction('fnmatch', function (string $pattern, string $string): bool {
            return fnmatch($pattern, $string);
        }));

        $this->template = $twig->load($path);
    }

    public function getForm(): ?FormInterface
    {
        return $this->form;
    }

    /**
     * @throws \Throwable
     */
    public function render(array $config = []): string
    {
        $defaultConfig = ['cpSession' => getenv('cp_security_token')];

        return $this->template->render(array_merge($config, $defaultConfig));
    }
}

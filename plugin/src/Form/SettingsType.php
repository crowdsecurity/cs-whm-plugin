<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * The settings form type class.
 *
 * @author    CrowdSec team
 *
 * @see      https://crowdsec.net CrowdSec Official Website
 *
 * @copyright Copyright (c) 2020+ CrowdSec
 * @license   MIT License
 */
class SettingsType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'lapi_port',
            IntegerType::class,
            array_merge(
                ['label' => 'LAPI port'],
                [
                    'required' => true,
                    'help' => 'Example: 8080 <br>
See /etc/crowdsec/config.yaml.local:<br>api:<br>&nbsp;&nbsp;server:<br>&nbsp;&nbsp;&nbsp;&nbsp;listen_uri:  127.0.0.1:&lt;port_number&gt;',
                    'help_html' => true
                ]
            )
        );

        $builder->add(
            'prometheus_port',
            IntegerType::class,
            array_merge(
                ['label' => 'Prometheus port'],
                ['required' => true,
                    'help' => 'Example: 6060 <br>
See /etc/crowdsec/config.yaml.local:<br>prometheus:<br>&nbsp;&nbsp;listen_port:&lt;port_number&gt;',
                    'help_html' => true]
            )
        );

        $builder->add('Save', SubmitType::class);
    }
}

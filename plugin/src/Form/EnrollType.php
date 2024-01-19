<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * The enroll form type class.
 *
 * @author    CrowdSec team
 *
 * @see      https://crowdsec.net CrowdSec Official Website
 *
 * @copyright Copyright (c) 2020+ CrowdSec
 * @license   MIT License
 */
class EnrollType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'key',
            TextType::class,
            array_merge(
                ['label' => 'Enrollment key'],
                [
                    'required' => true,
                    'help' => 'You can obtain your enrollment key in your ' .
                              '<a href="https://app.crowdsec.net/">CrowdSec console</a>',
                    'help_html' => true
                ]
            )
        );

        $builder->add(
            'name',
            TextType::class,
            array_merge(
                ['label' => 'Name'],
                ['required' => false, 'help' => 'Name to display in the console']
            )
        );

        $builder->add(
            'tags',
            TextareaType::class,
            array_merge(
                ['label' => 'Tags'],
                ['required' => false, 'help' => 'Tags to display in the console (one tag per line)']
            )
        );

        $builder->add(
            'overwrite',
            ChoiceType::class,
            array_merge(
                ['label' => 'Overwrite'],
                [
                    'required' => false,
                    'help' => 'Force enroll the instance',
                    'choices' => [
                        'No' => false,
                        'Yes' => true
                    ],
                    'data' => false, 'placeholder' => null
                ]
            )
        );

        $builder->add('enroll', SubmitType::class);
    }
}

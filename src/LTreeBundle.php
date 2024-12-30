<?php

namespace Pvsaintpe\LTreeBundle;

use Pvsaintpe\LTreeBundle\DqlFunction\{LTreeConcatFunction,
    LTreeNlevelFunction,
    LTreeOperatorFunction,
    LTreeSubpathFunction};
use Pvsaintpe\LTreeBundle\Types\LTreeType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class LTreeBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yml');
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $dbalConfig = [
            'dbal' => [
                'types' => [
                    LTreeType::TYPE_NAME => LTreeType::class,
                ],
                'mapping_types' => [
                    LTreeType::TYPE_NAME => LTreeType::TYPE_NAME
                ],
            ],
            'orm' => [
                'dql' => [
                    'string_functions' => [
                        LTreeConcatFunction::FUNCTION_NAME => LTreeConcatFunction::class,
                        LTreeSubpathFunction::FUNCTION_NAME => LTreeSubpathFunction::class
                    ],
                    'numeric_functions' => [
                        LTreeNlevelFunction::FUNCTION_NAME => LTreeNlevelFunction::class,
                        LTreeOperatorFunction::FUNCTION_NAME => LTreeOperatorFunction::class
                    ]
                ]
            ]
        ];

        $builder->prependExtensionConfig("doctrine", $dbalConfig);
    }
}

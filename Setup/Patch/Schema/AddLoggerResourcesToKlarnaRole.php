<?php

/**
 * Copyright © Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Logger\Setup\Patch\Schema;

use Klarna\Base\Model\Admin\KlarnaUserRoleManager;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

/**
 * @internal
 */
class AddLoggerResourcesToKlarnaRole implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var KlarnaUserRoleManager
     */
    private KlarnaUserRoleManager $klarnaUserRoleManager;

    /**
     * AddLoggerResourcesToKlarnaRole constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param KlarnaUserRoleManager $klarnaUserRoleManager
     * @codeCoverageIgnore
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        KlarnaUserRoleManager    $klarnaUserRoleManager
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->klarnaUserRoleManager = $klarnaUserRoleManager;
    }

    /**
     * There is no dependencies for this patch
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * There is no aliases for this patch
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Apply the patch
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $role = $this->klarnaUserRoleManager->loadRoleByName($this->klarnaUserRoleManager->getRoleName());
        if ($role->getId()) {
            $this->klarnaUserRoleManager->assignResourcesToRole($role->getId(), ['Klarna_Logger::system_logging']);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }
}

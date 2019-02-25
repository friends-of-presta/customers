<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

require_once __DIR__.'/vendor/autoload.php';

use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\BadgeColumn;

class customers extends Module
{
    /**
     * In constructor we define our module's meta data.
     * It's better tot keep constructor (and main module class itself) as thin as possible
     * and do any processing in controller.
     */
    public function __construct()
    {
        $this->name = 'customers';
        $this->version = '1.0.0';
        $this->author = 'Mickaël Andrieu';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = 'Enhanced customers module';
    }

    /**
     * Install module and register hooks to allow grid modification.
     *
     * @return bool
     */
    public function install()
    {

        $installStatus = parent::install() &&
            $this->registerHook('actionCustomerGridDefinitionModifier') &&
            $this->registerHook('actionCustomerGridQueryBuilderModifier')
        ;

        Tools::clearSf2Cache();

        return $installStatus;
    }

    /**
     * Install module and register hooks to allow grid modification.
     *
     * @return bool
     */
    public function uninstall()
    {
        $uninstallStatus = parent::uninstall() &&
            $this->unregisterHook('actionCustomerGridDefinitionModifier') &&
            $this->unregisterHook('actionCustomerGridQueryBuilderModifier')
        ;

        Tools::clearSf2Cache();

        return true;
    }

    /**
     * Hooks allows to modify Logs grid definition.
     * This hook is a right place to add/remove columns or actions (bulk, grid).
     *
     * @param array $params
     */
    public function hookActionCustomerGridDefinitionModifier(array $params)
    {
        /** @var GridDefinitionInterface $definition */
        $definition = $params['definition'];

        /** @var ColumnCollection */
        $columns = $definition->getColumns();
        $columns->remove('social_title')
            ->remove('active')
            ->remove('optin')
            ->remove('total_spent')
        ;

        $nbOrders = (new DataColumn('nb_orders'))
            ->setName($this->trans('Orders', [], 'Admin.Global'))
            ->setOptions([
                'field' => 'nb_orders',
            ])
        ;

        $sales = (new BadgeColumn('total_spent'))
            ->setName($this->trans('Sales', [], 'Admin.Global'))
            ->setOptions([
                'field' => 'total_spent',
                'empty_value' => '--',
            ]);

        $columns->addAfter('newsletter', $sales);
        $columns->addAfter('newsletter', $nbOrders);

        /** @var FilterCollection $filters */
        $filters = $definition->getFilters();

        $filters->remove('social_title')
            ->remove('active')
            ->remove('optin')
        ;
    }

    public function hookActionCustomerGridQueryBuilderModifier(array $params)
    {
        $searchQueryBuilder = $params['search_query_builder'];

        $searchQueryBuilder->addSelect('COUNT(o.id_order) as nb_orders')
            ->from('ps_orders o')
        ;
    }
}

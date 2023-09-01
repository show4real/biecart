<?php

namespace Plugins\PageBuilder\Addons\Tenants\Electro\Product;

use App\Enums\StatusEnums;
use App\Helpers\SanitizeInput;
use App\Models\OrderProducts;
use Illuminate\Support\Facades\DB;
use Modules\Attributes\Entities\Category;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductCategory;
use Plugins\PageBuilder\Fields\Image;
use Plugins\PageBuilder\Fields\NiceSelect;
use Plugins\PageBuilder\Fields\Number;
use Plugins\PageBuilder\Fields\Select;
use Plugins\PageBuilder\Fields\Switcher;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\PageBuilderBase;

class NewProducts extends PageBuilderBase
{
    public function preview_image()
    {
        return 'Tenant/common/brand-01.png';
    }

    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();
        $widget_saved_values = $this->get_settings();

        $output .= Text::get([
            'name' => 'title',
            'label' => __('Section Title'),
            'value' => $widget_saved_values['title'] ?? null,
        ]);

        $output .= Number::get([
            'name' => 'item_show',
            'label' => __('Product Show'),
            'value' => $widget_saved_values['item_show'] ?? null,
            'info' => 'How many products will be shown'
        ]);

        $output .= Select::get([
            'name' => 'item_order',
            'label' => __('Product Order'),
            'options' => [
                'desc' => __('Descending'),
                'asc' => __('Ascending')
            ],
            'value' => $widget_saved_values['item_order'] ?? null,
            'info' => 'Product order, descending or ascending'
        ]);

        // add padding option
        $output .= $this->padding_fields($widget_saved_values);
        $output .= $this->admin_form_submit_button();
        $output .= $this->admin_form_end();
        $output .= $this->admin_form_after();

        return $output;
    }

    public function frontend_render()
    {
        $title = esc_html($this->setting_item('title') ?? '');
        $item_show = esc_html($this->setting_item('item_show'));
        $item_order = esc_html($this->setting_item('item_order') ?? '');

        $padding_top = esc_html($this->setting_item('padding_top'));
        $padding_bottom = esc_html($this->setting_item('padding_bottom'));

        $products = Product::with('badge', 'campaign_product', 'inventory', 'inventoryDetail')
            ->published()
            ->orderBy('id', $item_order ?? 'desc')
            ->take(!empty($item_show) ? $item_show : 3)->get();

        $data = [
            'padding_top'=> $padding_top,
            'padding_bottom'=> $padding_bottom,
            'title' => $title,
            'products'=> $products,
        ];

        return self::renderView('tenant.electro.product.new-products', $data);
    }

    public function addon_title()
    {
        return __('Electro: New Products');
    }
}

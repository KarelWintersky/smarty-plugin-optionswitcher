<?php

namespace Arris\Plugin;

use Smarty;

/**
 *
 */
class OptionSwitcher
{
    /**
     * @var Smarty instance
     */
    private static Smarty $smarty;

    /**
     * Регистрирует в Smarty обработчик "макроса" OptionSwitcher
     *
     * @param Smarty $smarty
     * @param string $macros_name
     * @return void
     */
    public static function register(Smarty $smarty, string $macros_name = "OptionSwitcher")
    {
        self::$smarty = $smarty;
        self::$smarty->registerClass("OptionSwitcher", __CLASS__);
    }

    /**
     * Smarty-макрос вывода HTML-кода свитчера
     *
     * {OptionSwitcher::html('switcher_id', 'exposed_smarty_variable', [true,  ['on_text', ['off_text', ['css_tuning_class', [ uri ]]]]]) }
     *
     * @param string $switcher_id required
     * @param string $exposed_smarty_variable required
     * @param bool $is_enabled
     * @param string $on_text - default 'ДА'
     * @param string $off_text - default 'НЕТ'
     * @param string $css_tuning_class
     * @param string $request_uri
     * @return string
     */
    public static function html(string $switcher_id = '', string $exposed_smarty_variable = '', bool $is_enabled = true, string $on_text = 'ДА', string $off_text = 'НЕТ', string $css_tuning_class = '', string $request_uri = ''): string
    {
        if (empty($switcher_id) || empty($exposed_smarty_variable)) {
            return '';
        }

        // получаем значение "проброшенной" в шаблон переменной
        $is_checked = self::$smarty->getTemplateVars($exposed_smarty_variable) ? 'checked="checked"' : "";
        $custom_url = $request_uri ? "data-url=\"{$request_uri}\"" : "";
        $is_disabled = $is_enabled ? "" : "disabled";

        // Smarty печатает результат return'а
        return <<<HTML
    <input class="switcher action-option-switcher {$css_tuning_class}" id="{$switcher_id}" type="checkbox" {$is_checked} {$custom_url} {$is_disabled}>
    <label for="{$switcher_id}" data-text-true="{$on_text}" data-text-false="{$off_text}" data-disabled="{$is_disabled}"><i></i></label>
HTML;
    }

    /**
     * @param string $width
     * @param string $off_color
     * @param string $on_color
     * @param string $disabled_color
     * @return string
     */
    public static function css(string $width = "80", string $off_color = '#DB574D', string $on_color = '#67B04F', string $disabled_color = '#ACACAC'): string
    {
        return <<<CSS
<style>
/* Стили для свитчера */
input.switcher[type=checkbox] {
    display: none;
}

input.switcher[type=checkbox] + label {
    display: inline-block;
    background-color: #DB574D; /* $off_color */
    color: white;
    font-family: sans-serif;
    font-size: 14px;
    font-weight: bold;
    height: 30px;
    line-height: 30px;
    position: relative;
    text-transform: uppercase;
    width: 80px; /* $width */
}

input.switcher[type=checkbox] + label,
input.switcher[type=checkbox] + label i {
    -webkit-transition: all 200ms ease;
    -moz-transition: all 200ms ease;
    -o-transition: all 200ms ease;
    transition: all 200ms ease;
}

input.switcher[type=checkbox]:checked + label {
    background-color: #67B04F; /* $on_color */
}

input.switcher[type=checkbox] + label:before,
input.switcher[type=checkbox] + label:after,
input.switcher[type=checkbox] + label i {
    width: 50%;
    display: inline-block;
    height: 100%;
    text-align: center;
}

input.switcher[type=checkbox] + label:before {
    content: attr(data-text-true);
}

input.switcher[type=checkbox] + label:after {
    content: attr(data-text-false);
}

input.switcher[type=checkbox] + label i {
    top: 10%;
    background-color: white;
    height: 80%;
    left: 5%;
    position: absolute;
    width: 45%;
}

input.switcher[type=checkbox]:checked + label i {
    left: 50%;
}

input.switcher[type="checkbox"] + label[data-disabled*="disabled"] {
    background-color: #acacac !important; /* $disabled_color */
}
</style>
CSS;
    }

    /**
     * Дефолтный метод вывода JS-кода обработчика. Требует JQuery любой версии
     *
     * @param string $uri
     * @return string
     */
    public static function js(string $uri = '/options/switch:option'):string
    {
        return <<<JS
<script>
    $(document).ready(function (){
        $(".action-option-switcher").on('change', function(){
            let switcher = $(this).prop('id');

            let new_state = $('#' + switcher).is(':checked') ? 1 : 0;

            let url = $(this).data('url') || '{$uri}';

            $.ajax({
                url: url,
                method: "POST",
                data: {
                    switcher: switcher,
                    new_state: new_state
                }
            });

        });
    });
</script>
JS;
    }


}
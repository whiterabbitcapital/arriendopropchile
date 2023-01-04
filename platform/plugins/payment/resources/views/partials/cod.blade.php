@if (get_payment_setting('status', 'cod') == 1)
    <li class="list-group-item">
        <input class="magic-radio js_payment_method" type="radio" name="payment_method" id="payment_cod"
               @if ($selecting == \Botble\Payment\Enums\PaymentMethodEnum::COD) checked @endif
               value="cod" data-bs-toggle="collapse" data-bs-target=".payment_cod_wrap" data-parent=".list_payment_method">
        <label for="payment_cod" class="text-start">{{ setting('payment_cod_name', trans('plugins/payment::payment.payment_via_cod')) }}</label>
        <div class="payment_cod_wrap payment_collapse_wrap collapse @if ($selecting == \Botble\Payment\Enums\PaymentMethodEnum::COD) show @endif" style="padding: 15px 0;">
            {!! BaseHelper::clean(setting('payment_cod_description')) !!}
        </div>
    </li>
@endif

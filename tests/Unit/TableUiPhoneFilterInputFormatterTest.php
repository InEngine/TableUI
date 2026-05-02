<?php

declare(strict_types=1);

use InEngine\TableUI\Support\TableUiPhoneFilterInputFormatter;

it('formats NANP partial and full national entry', function (): void {
    config()->set('tableui.column_types.phone.default_country_code', '1');

    expect(TableUiPhoneFilterInputFormatter::format(''))->toBe('')
        ->and(TableUiPhoneFilterInputFormatter::format('307'))->toBe('(307')
        ->and(TableUiPhoneFilterInputFormatter::format('307877'))->toBe('(307) 877')
        ->and(TableUiPhoneFilterInputFormatter::format('3078779505'))->toBe('(307) 877-9505');
});

it('formats leading country code 1 as +1 display', function (): void {
    config()->set('tableui.column_types.phone.default_country_code', '1');

    expect(TableUiPhoneFilterInputFormatter::format('1'))->toBe('+1 ')
        ->and(TableUiPhoneFilterInputFormatter::format('13078779505'))->toBe('+1 (307) 877-9505');
});

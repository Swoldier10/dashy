<?php

namespace Tests\Unit\Domains\Auth\Enums;

use App\Domains\Auth\Enums\Salutation;
use PHPUnit\Framework\TestCase;

class SalutationTest extends TestCase
{
    public function test_cases_have_lowercase_stored_values(): void
    {
        $this->assertSame('mr', Salutation::Mr->value);
        $this->assertSame('ms', Salutation::Ms->value);
        $this->assertSame('mx', Salutation::Mx->value);
        $this->assertSame('dr', Salutation::Dr->value);
    }

    public function test_label_returns_titlecase_form(): void
    {
        $this->assertSame('Mr', Salutation::Mr->label());
        $this->assertSame('Ms', Salutation::Ms->label());
        $this->assertSame('Mx', Salutation::Mx->label());
        $this->assertSame('Dr', Salutation::Dr->label());
    }

    public function test_options_returns_value_to_label_map_in_declaration_order(): void
    {
        $this->assertSame(
            ['mr' => 'Mr', 'ms' => 'Ms', 'mx' => 'Mx', 'dr' => 'Dr'],
            Salutation::options(),
        );
    }
}

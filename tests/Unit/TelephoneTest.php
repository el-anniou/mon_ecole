<?php

namespace Tests\Unit;

use App\Support\Telephone;
use PHPUnit\Framework\TestCase;

class TelephoneTest extends TestCase
{
    public function test_normalise_un_numero_international_decore(): void
    {
        $this->assertSame('261340000000', Telephone::international('+261 34 00 000 00'));
        $this->assertSame('261340000000', Telephone::international('00261-34-00-000-00'));
        $this->assertSame('33612345678', Telephone::international('+33 6.12.34.56.78'));
    }

    public function test_retourne_null_pour_les_entrees_vides_ou_trop_courtes(): void
    {
        $this->assertNull(Telephone::international(null));
        $this->assertNull(Telephone::international(''));
        $this->assertNull(Telephone::international('abc'));
        $this->assertNull(Telephone::international('12345'));
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: monkey_C
 * Date: 18-Aug-15
 * Time: 2:32 PM
 */

namespace App\Http\Transformers;

use League\Fractal\TransformerAbstract as Transformer;

class RawTransformer extends Transformer
{

    public function transform($raw)
    {
        return $raw;
    }
}
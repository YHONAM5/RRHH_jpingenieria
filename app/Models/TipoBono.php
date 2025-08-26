<?php
/**
 * Created by Reliese Model.
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TipoBono
 * @property int $idTipoBono
 * @property string $descripcion;
 *
 * @package App\Models
*/

class TipoBono extends Model
{
    protected $table = 'tipo_bono';
    protected $primaryKey = 'idTipoBono';
    public $timestamps = false;

    protected $casts = [
        'descripcion' => 'string'
    ];

    protected $fillable = [
        'descripcion',
    ];
}

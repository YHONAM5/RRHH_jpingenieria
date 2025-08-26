<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Bono
 *
 * @property int $idBonos
 * @property int|null $idContrato
 * @property int|null $idPeriodo
 * @property int|null $idGratificacion
 * @property int|null $idTipoBono
 * @property float|null $PagoHorasExtras
 * @property float|null $Reintegro
 * @property float|null $CantidadDias
 * @property float|null $Monto
 * @property string|null $CantidadHoras
 *
 * @package App\Models
 */
class Bono extends Model
{
	protected $table = 'bonos';
	protected $primaryKey = 'idBonos';
	public $timestamps = false;

	protected $casts = [
		'idContrato' => 'int',
		'idPeriodo' => 'int',
		'idGratificacion' => 'int',
        'idTipoBono' => 'int',
		'PagoHorasExtras' => 'float',
		'Reintegro' => 'float',
        'CantidadDias' => 'float',
        'Monto' => 'float',
        'CantidadHoras' => 'datetime'
	];

	protected $fillable = [
		'idContrato',
		'idPeriodo',
		'idGratificacion',
        'idTipoBono',
		'PagoHorasExtras',
		'Reintegro',
		'CantidadDias',
        'Monto',
        'CantidadHoras'
	];
}

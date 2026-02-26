<img src="imagenes/logo_redondo.png" alt="" class="logo">
<table width="100%" style="margin-top: 15px">
    <tr>
        <th colspan="6">CALENDARIO DE PAGOS</th>
    </tr>
    <tr>
        <th align="left">FECHA DES.</th>
        <td align="left">{{ $desembolso->fecha }}</td>
        <th align="left">Monto</th>
        <td align="left">S/.{{ number_format($solicitud->monto, 2) }}</td>
        <th align="left">Plazo</th>
        <td align="left">{{ $solicitud->plazo }} {{ $plazoTexto }}</td>
    </tr>
    <tr>
        <th align="left">Cliente</th>
        <td align="left" colspan="3">{{ $cliente->apenom }}</td>
        <th align="left">ID CREDITO</th>
        <td align="left">{{ $solicitud->id }}</td>
    </tr>
    <tr>
        <th align="left">DIR domic</th>
        <td colspan="5">{{ $cliente->direccion }}</td>
    </tr>
</table>

<table width="100%" class="mitabla">
    <tr>
        <th width="5%">N°</th>
        <th width="15%">FECHA PROG.</th>
        <th width="10%">DIA</th>
        <th width="10%">CUOTA</th>
        <th width="15%">MONTO PAGADO</th>
        <th>FEC. PAGO</th>
        <th>SALDO</th>
        <th>FIRMA</th>
    </tr>			
    <tr>
        <td colspan="6"></td>
        <td>S/.{{ number_format($solicitud->total, 2) }}</td>		
        <td></td>		
    </tr>
    @foreach($cuotapagos as $row)
        <tr>
            <td>{{ $row->nrocuota }}</td>
            <td>{{ \Carbon\Carbon::parse($row->fecha_prog)->format('d-m-Y') }}</td>
            <td>{{ $row->nombredia }}</td>
            <td>S/.{{ number_format($row->cuota, 2) }}</td>
            <td></td>
            <td></td>
            <td>S/.{{ number_format($row->saldo, 2) }}</td>
            <td></td>
        </tr>
    @endforeach
</table>

<table width="100%" class="pie">
    <tr>
        <th align="left" width="15%">ASESOR</th>
        <td align="left" width="50%">{{ $asesor?->apenom }}</td>
        <th align="left" width="15%">CELULAR</th>
        <td align="left" width="20%">{{ $asesor?->celular }}</td>
    </tr>
    <tr>
        <th align="left" width="15%">YAPE</th>
        <td class="align-top" width="50%">
        <span class="d-block fw-semibold">920275552</span><br>
        <span class="d-block fs-7 text-muted">KATHERINE E. ESPIRITU M.</span>
        </td>
        
        <td class="align-top" width="35%">
            <span class="d-block fw-semibold">966821222</span><br>
            <span class="d-block fs-7">
                TU Amigo Emprendedor 3e
            </span>
        </td>
    </tr>	
    <tr>
        <th align="left" width="15%"></th>
        <td align="left" width="50%"></td>
        <th align="left" width="15%">BCP</th>
        <td align="left" width="20%">365 2486979065</td>
    </tr>	
    <tr>
        <td colspan="2" style="font-size: 10px;">
            Por cada día de atraso se le cobrará una mora de S/.{{ number_format($solicitud->costomora, 2) }}
        </td>
        <td colspan="2" style="font-size: 14px;">
            
        </td>
    </tr>
</table>
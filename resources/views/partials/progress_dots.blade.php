@php
    /** $items: [['name','role','status','order_no'], ...] */
@endphp

@if(empty($items))
    <span class="btn btn-default btn-icon btn-xs"
          style="width:12px;height:12px;margin-right:3px;padding:0;border-radius:2px;">
    </span>
@else

@foreach ($items as $it)

    @php
        $cls = 'btn-default';

        if (in_array($it['status'], [1,10], true)) {
            $cls = 'btn-warning'; // Assigned
        }
        elseif (in_array($it['status'], [2,20], true)) {
            $cls = 'btn-success'; // In Progress
        }
        elseif (in_array($it['status'], [3,30], true)) {
            $cls = 'btn-info'; // Done
        }
    @endphp

    <span class="btn {{ $cls }} btn-icon btn-xs"
          data-toggle="tooltip"
          title="{{ $it['name'] }} ({{ $it['role'] }})"
          style="width:12px;height:12px;margin-right:3px;padding:0;border-radius:2px;">
    </span>

@endforeach

@endif
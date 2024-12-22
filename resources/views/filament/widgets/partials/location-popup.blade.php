<div class='p-2'>
    <h3 class='font-bold'>{{ $location->customer_name }}</h3>
    <p class='text-sm text-gray-600'>{{ $location->address }}</p>
    <p class='text-sm text-gray-600'>{{ $location->city }}, {{ $location->postal_code }}</p>
    <p class='text-sm text-gray-600'>Phone: {{ $location->phone_number }}</p>
    <p class='mt-2'>
        <span class='px-2 py-1 text-xs rounded-full 
            @if($location->address_type === 'Rumah')
                bg-green-100 text-green-800
            @elseif($location->address_type === 'Kantor')
                bg-blue-100 text-blue-800
            @elseif($location->address_type === 'Apartemen')
                bg-yellow-100 text-yellow-800
            @elseif($location->address_type === 'Kos')
                bg-purple-100 text-purple-800
            @else
                bg-gray-100 text-gray-800
            @endif
        '>
            {{ $location->address_type }}
        </span>
    </p>
</div>

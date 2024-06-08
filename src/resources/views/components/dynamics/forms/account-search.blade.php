@props([
    'action' => '_searchAccounts',
    'result' => 'account_result',
    'query' => 'account_search',
    'searchCol' => 6,
    'selectedId' => 'account_id',
])
<div class="py-2">
    <div class="d-flex justify-content-center">

        <x-dynamics.forms.input col="{{ $searchCol }}" model='{{ $query }}' type='text'
            placeholder="Enter Account No" label='Account No' :hasLabel="false">
            @slot('append')
                <x-dynamics.button action="_searchAccounts('{{ $result }}','{{ $query }}' )" text="Search"
                    :hasIcon="true" icon="bx bx-search" :hasLoader="true" customProcessmessage="Searching..."
                    :isOutlined="true" />
            @endslot
        </x-dynamics.forms.input>
    </div>
    <div class="d-flex justify-content-center">
        @error($query)
            <div class="text-danger">{{ $message }}</div>
        @enderror
        @error($selectedId)
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    @if ($this->$result)
        <x-dynamics.table :paginate="false">
            <x-slot name="thead">
                <th>Account Name</th>
                <th>Ac No</th>
                <th>Main Owner</th>
                <th>Currency</th>
                <th>Action</th>
            </x-slot>
            <x-slot name="tbody">
                @foreach ($this->$result as $data)
                    <tr>
                        <td>{{ $data->fullname }}</td>
                        <td>{{ $data->ac_no }}</td>
                        <td>{{ $data->user->full_name }}</td>
                        <td>{{ $data->currency }}</td>
                        <td class="py-1">
                            <button wire:click="$set('{{ $selectedId }}',{{ $data->id }})"
                                class="btn btn-{{ $this->$selectedId == $data->id ? 'primary' : 'outline-primary' }} btn-sm">
                                <i class="fa fa-check"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </x-slot>
        </x-dynamics.table>
    @endif
</div>

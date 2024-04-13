@extends('template.app')

@section('styles')
@endsection

@section('content')
<section class="section">
    <form action="{{ route('group-user.set-access-menu') }}" method="POST" id="myForm">
        @csrf
        <div class="section-header">
            <h1>{{ $title }} - {{ $subtitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">{{ $title }}</div>
                @if($submodule !== '')
                <div class="breadcrumb-item">{{ $submodule }}</div>
                @endif
                <div class="breadcrumb-item">{{ $subtitle }}</div>
            </div>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="d-flex justify-content-between">
                            <div class="card-header">
                                <h4>{{ __('setting_group_user')['list'] }} {{ $title }} - {{ $subtitle }}</h4>
                            </div>
                            <div class="d-flex justify-content-end align-items-center pr-3">
                                {!! $list_nav_button['back'] !!}
                                @if (isset($list_nav_button['save']))
                                    {!! $list_nav_button['save'] !!}
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="card-body">
                                @php
                                    $error_session = false;
                                    $id = request()->route('id');
                                @endphp
                                {!! renderInput('mb-3 row', 'col-lg-4 col-form-label', 'col-lg-8', 'hidden', 'user_group_id', '', $id, $mode, 'readonly') !!}
                                @if (session('errors'))
                                    @php
                                    $error_session = true;
                                    @endphp
                                @endif
                                <div>
                                    <table id="example" class="stripe cell-border table table-responsive table-bordered">
                                        <thead>
                                            <tr>
                                                <th scope="col"></th>
                                                <th scope="col" data-orderable="false">{{ __('setting_group_user')['col_menu'] }}</th>
                                                <th scope="col" data-orderable="false">{{ __('setting_group_user')['col_select_all'] }}</th>
                                                <th scope="col" data-orderable="false">{{ __('setting_group_user')['col_open'] }} {!! renderCheckbox('form-check mb-2', 'form-check-input checkAllVertical', 'select_all_open', 'select_all_open', 'data-id="open"') !!}</th>
                                                <th scope="col" data-orderable="false">{{ __('setting_group_user')['col_add'] }} {!! renderCheckbox('form-check mb-2', 'form-check-input checkAllVertical', 'select_all_add', 'select_all_add', 'data-id="add"') !!}</th>
                                                <th scope="col" data-orderable="false">{{ __('setting_group_user')['col_edit'] }} {!! renderCheckbox('form-check mb-2', 'form-check-input checkAllVertical', 'select_all_edit', 'select_all_edit', 'data-id="edit"') !!}</th>
                                                <th scope="col" data-orderable="false">{{ __('setting_group_user')['col_delete'] }} {!! renderCheckbox('form-check mb-2', 'form-check-input checkAllVertical', 'select_all_delete', 'select_all_delete', 'data-id="delete"') !!}</th>
                                                <th scope="col" data-orderable="false">{{ __('setting_group_user')['col_print'] }} {!! renderCheckbox('form-check mb-2', 'form-check-input checkAllVertical', 'select_all_print', 'select_all_print', 'data-id="print"') !!}</th>
                                                <th scope="col" data-orderable="false">{{ __('setting_group_user')['col_approve'] }} {!! renderCheckbox('form-check mb-2', 'form-check-input checkAllVertical', 'select_all_approve', 'select_all_approve', 'data-id="approve"') !!}</th>
                                                <th scope="col" data-orderable="false">{{ __('setting_group_user')['col_disapprove'] }} {!! renderCheckbox('form-check mb-2', 'form-check-input checkAllVertical', 'select_all_disapprove', 'select_all_disapprove', 'data-id="disapprove"') !!}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $encode_menu = Session::get('list_menu');
                                            @endphp
                                            @if (!empty($encode_menu))
                                                @foreach ($encode_menu as $item)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $item['title'] }}</td>
                                                    <td>{!! renderCheckbox('form-check mb-2', 'checkAllHorizontal form-check-input', 'select_all', 'select_all', 'data-id="' . $item['id'] . '"') !!}</td>

                                                    @if ($access_menu == null)
                                                        @if ($item['is_sidebar'] == true)
                                                            <td>{!! renderCheckbox('form-check mb-2', 'open form-check-input', 'open-'.$item['id'].'', 'menus['.$item['id'].'][open]', '', '') !!}</td>
                                                            <td>{!! renderCheckbox('form-check mb-2', 'add form-check-input', '', 'menus['.$item['id'].'][add]', 'disabled', '') !!}</td>
                                                            <td>{!! renderCheckbox('form-check mb-2', 'edit form-check-input', '', 'menus['.$item['id'].'][edit]', 'disabled', '') !!}</td>
                                                            <td>{!! renderCheckbox('form-check mb-2', 'delete form-check-input', '', 'menus['.$item['id'].'][delete]', 'disabled', '') !!}</td>
                                                            <td>{!! renderCheckbox('form-check mb-2', 'print form-check-input', '', 'menus['.$item['id'].'][print]', 'disabled', '') !!}</td>
                                                            <td>{!! renderCheckbox('form-check mb-2', 'approve form-check-input', '', 'menus['.$item['id'].'][approve]', 'disabled', '') !!}</td>
                                                            <td>{!! renderCheckbox('form-check mb-2', 'disapprove form-check-input', '', 'menus['.$item['id'].'][disapprove]', 'disabled', '') !!}</td>
                                                        @else
                                                            <td>{!! renderCheckbox('form-check mb-2', 'open form-check-input', 'open-'.$item['id'].'', 'menus['.$item['id'].'][open]', '', $error_session ? (old('menus.' . $item['id'] . '.open')) : '') !!}</td>
                                                            <td>{!! renderCheckbox('form-check mb-2', 'add form-check-input', 'add-'.$item['id'].'', 'menus['.$item['id'].'][add]', '', $error_session ? (old('menus.' . $item['id'] . '.add')) : '') !!}</td>
                                                            <td>{!! renderCheckbox('form-check mb-2', 'edit form-check-input', 'edit-'.$item['id'].'', 'menus['.$item['id'].'][edit]', '', $error_session ? (old('menus.' . $item['id'] . '.edit')) : '') !!}</td>
                                                            <td>{!! renderCheckbox('form-check mb-2', 'delete form-check-input', 'delete-'.$item['id'].'', 'menus['.$item['id'].'][delete]', '', $error_session ? (old('menus.' . $item['id'] . '.delete')) : '') !!}</td>
                                                            <td>{!! renderCheckbox('form-check mb-2', 'print form-check-input', 'print-'.$item['id'].'', 'menus['.$item['id'].'][print]', '', $error_session ? (old('menus.' . $item['id'] . '.print')) : '') !!}</td>
                                                            <td>{!! renderCheckbox('form-check mb-2', 'approve form-check-input', 'approve-'.$item['id'].'', 'menus['.$item['id'].'][approve]', '', $error_session ? (old('menus.' . $item['id'] . '.approve')) : '') !!}</td>
                                                            <td>{!! renderCheckbox('form-check mb-2', 'disapprove form-check-input', 'disapprove-'.$item['id'].'', 'menus['.$item['id'].'][disapprove]', '', $error_session ? (old('menus.' . $item['id'] . '.disapprove')) : '') !!}</td>
                                                        @endif
                                                    @else
                                                        @php
                                                            $countAccessMenu = count($access_menu);
                                                            $i = 1;
                                                        @endphp

                                                        @foreach ($access_menu as $value)
                                                            @if($value['menu_id'] == $item['id'])
                                                                @if ($item['is_sidebar'] == true)
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'open form-check-input', 'open-'.$item['id'].'', 'menus['.$item['id'].'][open]', '', $error_session ? (old('menus.' . $item['id'] . '.open')) : ($value['open'] ? 'checked' : '')) !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'add form-check-input', 'add-'.$item['id'].'', 'menus['.$item['id'].'][add]', 'disabled', $error_session ? (old('menus.' . $item['id'] . '.add')) : ($value['add'] ? 'checked' : '')) !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'edit form-check-input', 'edit-'.$item['id'].'', 'menus['.$item['id'].'][edit]', 'disabled', $error_session ? (old('menus.' . $item['id'] . '.edit')) : ($value['edit'] ? 'checked' : '')) !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'delete form-check-input', 'delete-'.$item['id'].'', 'menus['.$item['id'].'][delete]', 'disabled', $error_session ? (old('menus.' . $item['id'] . '.delete')) : ($value['delete'] ? 'checked' : '')) !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'print form-check-input', 'print-'.$item['id'].'', 'menus['.$item['id'].'][print]', 'disabled', $error_session ? (old('menus.' . $item['id'] . '.print')) : ($value['print'] ? 'checked' : '')) !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'approve form-check-input', 'approve-'.$item['id'].'', 'menus['.$item['id'].'][approve]', 'disabled', $error_session ? (old('menus.' . $item['id'] . '.approve')) : ($value['approve'] ? 'checked' : '')) !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'disapprove form-check-input', 'disapprove-'.$item['id'].'', 'menus['.$item['id'].'][disapprove]', 'disabled', $error_session ? (old('menus.' . $item['id'] . '.disapprove')) : ($value['disapprove'] ? 'checked' : '')) !!}</td>
                                                                @break;
                                                                @else
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'open form-check-input', 'open-'.$item['id'].'', 'menus['.$item['id'].'][open]', '', $error_session ? (old('menus.' . $item['id'] . '.open')) : ($value['open'] ? 'checked' : '')) !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'add form-check-input', 'add-'.$item['id'].'', 'menus['.$item['id'].'][add]', '', $error_session ? (old('menus.' . $item['id'] . '.add')) : ($value['add'] ? 'checked' : '')) !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'edit form-check-input', 'edit-'.$item['id'].'', 'menus['.$item['id'].'][edit]', '', $error_session ? (old('menus.' . $item['id'] . '.edit')) : ($value['edit'] ? 'checked' : '')) !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'delete form-check-input', 'delete-'.$item['id'].'', 'menus['.$item['id'].'][delete]', '', $error_session ? (old('menus.' . $item['id'] . '.delete')) : ($value['delete'] ? 'checked' : '')) !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'print form-check-input', 'print-'.$item['id'].'', 'menus['.$item['id'].'][print]', '', $error_session ? (old('menus.' . $item['id'] . '.print')) : ($value['print'] ? 'checked' : '')) !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'approve form-check-input', 'approve-'.$item['id'].'', 'menus['.$item['id'].'][approve]', '', $error_session ? (old('menus.' . $item['id'] . '.approve')) : ($value['approve'] ? 'checked' : '')) !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'disapprove form-check-input', 'disapprove-'.$item['id'].'', 'menus['.$item['id'].'][disapprove]', '', $error_session ? (old('menus.' . $item['id'] . '.disapprove')) : ($value['disapprove'] ? 'checked' : '')) !!}</td>
                                                                @break;
                                                                @endif
                                                            @else
                                                                @if ($countAccessMenu == $i)
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'open form-check-input', 'open-'.$item['id'].'', 'menus['.$item['id'].'][open]', '', $error_session ? (old('menus.' . $item['id'] . '.open')) : '') !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'add form-check-input', 'add-'.$item['id'].'', 'menus['.$item['id'].'][add]', '', $error_session ? (old('menus.' . $item['id'] . '.add')) : '') !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'edit form-check-input', 'edit-'.$item['id'].'', 'menus['.$item['id'].'][edit]', '', $error_session ? (old('menus.' . $item['id'] . '.edit')) : '') !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'delete form-check-input', 'delete-'.$item['id'].'', 'menus['.$item['id'].'][delete]', '', $error_session ? (old('menus.' . $item['id'] . '.delete')) : '') !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'print form-check-input', 'print-'.$item['id'].'', 'menus['.$item['id'].'][print]', '', $error_session ? (old('menus.' . $item['id'] . '.print')) : '') !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'approve form-check-input', 'approve-'.$item['id'].'', 'menus['.$item['id'].'][approve]', '', $error_session ? (old('menus.' . $item['id'] . '.approve')) : '') !!}</td>
                                                                    <td>{!! renderCheckbox('form-check mb-2', 'disapprove form-check-input', 'disapprove-'.$item['id'].'', 'menus['.$item['id'].'][disapprove]', '', $error_session ? (old('menus.' . $item['id'] . '.disapprove')) : '') !!}</td>
                                                                @break;
                                                                @else
                                                                    @php
                                                                        $i += 1;
                                                                    @endphp
                                                                @endif
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        setMenuActive();

        var inputIds = ['open', 'add', 'edit', 'delete', 'print', 'reject', 'approve', 'disapprove', 'close'];

        $('#example').on('click', 'input[class^="open"], input[class^="add"], input[class^="edit"], input[class^="delete"], input[class^="print"], input[class^="approve"], input[class^="disapprove"], input[class^="reject"], input[class^="close"]', function() {
            var category = $(this).attr('class').split(' ')[0]; // Get the category from the class attribute
            var id = $(this).attr('id').split('-')[1]; // Get the id from the class attribute
            var headerCheckbox = $('.checkAllVertical[data-id="' + category + '"]');
            var rowCheckboxes = $('input[id^="' + category + '-' + id + '"]');

            // Check if any row checkbox in the category is unchecked
            var unchecked = $('input[class^="' + category + '"]').filter(':not(:checked)').length > 0;
            var uncheckedRow = true;

            for (var i = 0; i < inputIds.length; i++) {
                var inputId = inputIds[i];
                var selector = 'input[id^="' + inputId + '-' + id + '"]'; // Select all inputs with ID starting with the specific inputId
                var rowCheckboxes = $(selector);

                // Check if any of the row checkboxes for this inputId is unchecked
                if (rowCheckboxes.filter(':not(:checked)')) {
                    uncheckedRow = true;
                    break; // Exit the loop early if any checkbox is unchecked
                }
            }


            // Update the header checkbox state
            headerCheckbox.prop('checked', !unchecked);

            var checkAllHorizontalCheckbox = $('.checkAllHorizontal[data-id="' + id + '"]');
            checkAllHorizontalCheckbox.prop('checked', !uncheckedRow);
        });

        // Handle click on header checkboxes to update row checkboxes
        $('.checkAllVertical').on('click', function() {
            var category = $(this).data('id');
            var checked = $(this).prop('checked');

            $('input[id^="' + category + '"]').prop('checked', checked);
        });

        // Initialize header checkboxes
        $('.checkAllVertical').each(function() {
            var category = $(this).data('id');
            var checked = true;

            if ($('input[class^="' + category + '"]').filter(':not(:checked)').length > 0) {
                checked = false;
            }

            $(this).prop('checked', checked);
        });

        $('#example').on('click', '.checkAllHorizontal', function() {
            // Get the ID from the data attribute
            var id = $(this).data('id');
            console.log("Clicked checkbox with ID: " + id);

            // Check/uncheck other checkboxes with the same ID
            for (var i = 0; i < inputIds.length; i++) {
                var inputId = inputIds[i];
                var selector = 'input[id="' + inputId + '-' + id + '"]';
                $(selector).prop('checked', $(this).prop('checked'));
            }
        });
    });
</script>
@endsection
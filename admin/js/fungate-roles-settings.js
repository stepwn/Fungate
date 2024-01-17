jQuery(document).ready(function($) {
    $('#fungate_save_btn').on('click', function() {
        var data = {
            'action': 'save_fungate_roles',
            'nonce_field_name': fungate_vars.nonce,
            'data': $('#fungate_roles_form').serialize()
        };
        $.post(fungate_vars.ajax_url, data, function(response) {
            alert('Settings saved');
        });
    });
    $('#selected_role').on('change', function() {
        var selectedRole = $(this).val();
        $('.fungate-role-inputs').hide();
        $('.fungate-role-inputs[data-role="' + selectedRole + '"]').show();
    });

});
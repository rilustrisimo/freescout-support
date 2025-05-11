/**
 * Module's JavaScript.
 */

function initApiWebhooksSettings(msg_confirm_delete, msg_mailboxes_hint)
{
	$(document).ready(function() {

		$(".apiwh-delete").click(function(e){
			var button = $(this);

			showModalConfirm(msg_confirm_delete, 'apiwh-delete-ok', {
				on_show: function(modal) {
					var webhook_id = button.attr('data-webhook_id');

					if (!webhook_id) {
						return;
					}

					modal.children().find('.apiwh-delete-ok:first').click(function(e) {
						button.button('loading');
						modal.modal('hide');
						fsAjax(
							{
								action: 'delete',
								webhook_id: webhook_id
							}, 
							laroute.route('apiwebhooks.ajax'), 
							function(response) {
								if (isAjaxSuccess(response)) {
									window.location.href = '';
								} else {
									showAjaxResult(response);
									button.button('reset');
								}
							}
						);
					});
				}
			}, Lang.get("messages.delete"));

			e.preventDefault();
		});

		$(".apiwh-input-events").select2();
		$(".apiwh-input-mailboxes").select2({
			placeholder: msg_mailboxes_hint
		});
	});
}

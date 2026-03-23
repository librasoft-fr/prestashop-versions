{**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 *}
{if isset($order_invoice->note) && $order_invoice->note}
	<tr>
		<td colspan="12" height="10">&nbsp;</td>
	</tr>

	<tr>
		<td colspan="6" class="left">
			<table id="note-tab" style="width: 100%">
				<tr>
					<td class="grey">{l s='Note' d='Shop.Pdf' pdf='true'}</td>
				</tr>
				<tr>
					<td class="note">{$order_invoice->note|escape:"html"}</td>
				</tr>
			</table>
		</td>
		<td colspan="1">&nbsp;</td>
	</tr>
{/if}

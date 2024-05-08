<?php

/**
 * Gateway CL
 *
 * @package     GatewayCL
 * @author      Henri Susanto
 * @copyright   2024 Henri Susanto
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Gateway CL
 * Plugin URI:  https://github.com/susantohenri/gateway-cl
 * Description: WordPress Plugin to use Gateway CL API
 * Version:     1.0.0
 * Author:      Henri Susanto
 * Author URI:  https://github.com/susantohenri/
 * Text Domain: GatewayCL
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

define('GATEWAY_CL_TRACKING_PAGE', site_url('tracking'));

add_shortcode('gatewaycl-tracking', function () {
    $message = '';
    $tracking_result = '';
    $code = '';

    wp_register_style('gateway-cl', plugin_dir_url(__FILE__) . 'gateway-cl.css', [], 1);
    wp_enqueue_style('gateway-cl');

    if (isset($_POST['search'])) {
        $code = $_POST['code'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://gateway-cl.com/api/track_local?X-API-KEY=gateway-fms&si_number={$code}&shipper=PT.%20KARYA%20MULIA%20LOGISTIC");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $json = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($json);
        if (is_null($json)) $message = "Error: not found!";
        else {
            $last_status = '';
            $last_update = '';
            $departure_from = '';
            $arrival_at = '';
            $vessel_name = '';
            $from = '';
            $to = '';

            if (isset($json->header)) {
                if (isset($json->header[0])) {
                    $last_status = isset($json->header[0]->status) ? $json->header[0]->status : '';
                    $last_update = isset($json->header[0]->last_update) ? date('M d, Y H:i', strtotime($json->header[0]->last_update)) : '';
                }
            }
            if (isset($json->routing)) {
                if (isset($json->routing[0])) {
                    $from = isset($json->routing[0]->port_of_loading) ? strtoupper($json->routing[0]->port_of_loading) : '';
                    $to = isset($json->routing[0]->port_of_discharge) ? strtoupper($json->routing[0]->port_of_discharge) : '';
                    $departure_from = isset($json->routing[0]->time_of_departure) ? $json->routing[0]->time_of_departure : '';
                    $arrival_at = isset($json->routing[0]->time_of_discharge) ? $json->routing[0]->time_of_discharge : '';
                    $vessel_name = isset($json->routing[0]->vessel) ? $json->routing[0]->vessel : '';
                }
            }

            $tracking_result = "
                <tr>
                    <td colspan=\"3\">&nbsp;</td>
                </tr>
                <tr>
                    <td>
                        Last Status<br>
                        <input type=\"text\" value=\"{$last_status}\" disabled>
                    </td>
                    <td>&nbsp;</td>
                    <td>
                        Last Update<br>
                        <input type=\"text\" value=\"{$last_update}\" disabled>
                    </td>
                </tr>
                <tr>
                    <td colspan=\"3\">&nbsp;</td>
                </tr>
                <tr>
                    <td>Departure From</td>
                    <td>Arrival At</td>
                    <td>Vessel Name</td>
                </tr>
                <tr>
                    <td>
                        {$from}<br>
                        {$departure_from}
                    </td>
                    <td>
                        {$to}<br>
                        {$arrival_at}
                    </td>
                    <td>{$vessel_name}<br>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan=\"3\">&nbsp;</td>
                </tr>
                <tr class=\"from-to\">
                    <td><div></div></td>
                    <td><hr></td>
                    <td><div></div></td>
                </tr>
                <tr>
                    <td>{$from}</td>
                    <td></td>
                    <td>{$to}</td>
                </tr>
                <tr>
                    <td colspan=\"3\">&nbsp;</td>
                </tr>
            ";
        }
    }

    $tracking_page_url = GATEWAY_CL_TRACKING_PAGE;
    return "
        <table width=\"100%\" id=\"gatewaycl_tracking\">
            <tr>
                <td colspan=\"3\">Tracking Shipment</td>
            </tr>
            <tr>
                <td colspan=\"3\">
                    <form method=\"POST\" action=\"{$tracking_page_url}\">
                        <input type=\"text\" name=\"code\" placeholder=\"Tracking Code\" required value=\"{$code}\">
                        <input type=\"submit\" name=\"search\" value=\"Search\">
                    </form>
                </td>
            </tr>
            <tr>
                <td colspan=\"3\" id=\"gatewaycl_tracking_error\">{$message}</td>
            </tr>
            {$tracking_result}
        </table>
    ";
});

add_shortcode('gatewaycl-tracking-widget', function () {
    wp_register_style('gateway-cl', plugin_dir_url(__FILE__) . 'gateway-cl.css', [], 1);
    wp_enqueue_style('gateway-cl');
    $tracking_page_url = GATEWAY_CL_TRACKING_PAGE;
    return "
        <table width=\"100%\" id=\"gatewaycl_tracking\">
            <tr>
                <td colspan=\"3\">Tracking Shipment</td>
            </tr>
            <tr>
                <td colspan=\"3\">
                    <form method=\"POST\" action=\"{$tracking_page_url}\">
                        <input type=\"text\" name=\"code\" placeholder=\"Tracking Code\" required>
                        <input type=\"submit\" name=\"search\" value=\"Search\">
                    </form>
                </td>
            </tr>
            <tr>
                <td colspan=\"3\">&nbsp;</td>
            </tr>
        </table>
    ";
});

add_shortcode('gatewaycl-export-schedule', function () {
    wp_register_style('gateway-cl', plugin_dir_url(__FILE__) . 'gateway-cl.css', [], 1);
    wp_enqueue_style('gateway-cl');

    $month_year = date('F Y', time());

    return "
        <table width=\"100%\" class=\"gatewaycl-export-schedule\">
            <tr class=\"gatewaycl-export-schedule-form\">
                <td>
                    <select name=\"origin_name\">
                        <option value=\"JAKARTA\">JAKARTA</option>
                    </select>
                    <select name=\"destination_name\">
                        <option value=\"SYDNEY\">SYDNEY</option>
                    </select>
                    <select name=\"etd\">
                        <option value=\"04-05-2024\">04-05-2024</option>
                    </select>
                    <input type=\"submit\" name=\"search\" value=\"Search\">
                    <input type=\"reset\" value=\"Reset\">
                </td>
            </tr>
            <tr class=\"gatewaycl-export-schedule-month-year\">
                <td><h3>{$month_year}</h3></td>
            </tr>
            <tr class=\"gatewaycl-export-schedule-departure\">
                <td>
                    <div>
                        Departure From
                        <select name=\"origin_name\">
                            <option value=\"JAKARTA\">JAKARTA</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr class=\"gatewaycl-export-schedule-static-message\">
                <td>
                    The estimated schedule is considered for information purpose only and the Carrier may update, revise this schedule from time to time without any prior notice.
                </td>
            </tr>
            <tr class=\"gatewaycl-export-schedule-tables\"></tr>
        </table>
    ";
});

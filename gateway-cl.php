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
define('GATEWAY_CL_IMPORT_SCHEDULE_PAGE', site_url('import-schedule'));

add_shortcode('gatewaycl-tracking', function () {
    $message = '';
    $tracking_result = '';
    $code = '';

    wp_register_style('gateway-cl', plugin_dir_url(__FILE__) . 'gateway-cl.css', [], 3);
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
    wp_register_style('gateway-cl', plugin_dir_url(__FILE__) . 'gateway-cl.css', [], 3);
    wp_enqueue_style('gateway-cl');
    $tracking_page_url = GATEWAY_CL_TRACKING_PAGE;
    return "
        <table width=\"100%\" id=\"gatewaycl_tracking\">
            <tr>
                <td>Tracking Shipment</td>
            </tr>
            <tr>
                <td>
                    <form method=\"POST\" action=\"{$tracking_page_url}\">
                        <input type=\"text\" name=\"code\" placeholder=\"Tracking Code\" required>
                        <input type=\"submit\" name=\"search\" value=\"Search\">
                    </form>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
        </table>
    ";
});

add_shortcode('gatewaycl-export-schedule', function () {
    wp_register_style('gateway-cl', plugin_dir_url(__FILE__) . 'gateway-cl.css', [], 3);
    wp_enqueue_style('gateway-cl');

    $month_year = date('F Y', time());

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://gateway-cl.com/api/schedule?X-API-KEY=gateway-fms");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $json = curl_exec($ch);
    curl_close($ch);
    $schedule = json_decode($json);
    $tables_via = [];
    $tables_direct = [];
    $tables_html = '';

    $via = $schedule->data[0]->via;
    foreach (array_values(array_unique(array_map(function ($record) {
        return $record->destination_name;
    }, $via))) as $table_title) {
        $tables_via[$table_title] = array_values(array_filter($via, function ($record) use ($table_title) {
            return $table_title == $record->destination_name;
        }));
    }

    foreach ($tables_via as $table_title => $records) {
        $tables_html .= "<table>";
        $tables_html .= "<tr><td colspan=\"9\" class=\"table-title\">{$table_title}</td></tr>";

        $tables_html .= "<tr class=\"column-name\">";
        foreach (['VESSEL', 'VOY', 'STF/CLS', 'ETD', 'VES CONNECTING', 'VOY CONNECTING', 'ETD', 'CONNECTING CITY', 'ETA'] as $thead) {
            $tables_html .= "<td>{$thead}</td>";
        }
        $tables_html .= "</tr>";

        foreach ($records as $record) {
            $tables_html .= "<tr class=\"data\" data-origin_name = \"$record->origin_name\" data-destination_name = \"$record->destination_name\" data-etd = \"$record->etd_jkt\">";
            foreach (['vessel', 'voy_vessel', 'stf_cls', 'etd_jkt', 'connecting_vessel', 'voy_con', 'etd_con', 'etd_city_con_name', 'eta'] as $attribute) {
                if (in_array($attribute, ['stf_cls', 'etd_jkt', 'etd_con', 'eta'])) $record->$attribute = date('d M', strtotime($record->$attribute));
                if ('etd_city_con_name' == $attribute) $record->$attribute = substr($record->$attribute, 0, 3);
                $tables_html .= "<td>{$record->$attribute}</td>";
            }
            $tables_html .= "</tr>";
        }

        $tables_html .= "</table>";
    }

    $direct = $schedule->data[0]->direct;
    foreach (array_values(array_unique(array_map(function ($record) {
        return $record->destination_name;
    }, $direct))) as $table_title) {
        $tables_direct[$table_title] = array_values(array_filter($direct, function ($record) use ($table_title) {
            return $table_title == $record->destination_name;
        }));
    }
    foreach ($tables_direct as $table_title => $records) {
        $tables_html .= "<table class=\"table-direct\">";
        $tables_html .= "<tr><td colspan=\"5\" class=\"table-title\">{$table_title}</td></tr>";

        $tables_html .= "<tr class=\"column-name\">";
        foreach (['VESSEL', 'VOY', 'STF/CLS', 'ETD', 'ETA'] as $thead) {
            $tables_html .= "<td>{$thead}</td>";
        }
        $tables_html .= "</tr>";

        foreach ($records as $record) {
            $tables_html .= "<tr class=\"data\" data-origin_name = \"$record->origin_name\" data-destination_name = \"$record->destination_name\" data-etd = \"$record->etd\">";
            foreach (['vessel', 'voyage', 'closing_date', 'etd', 'eta'] as $attribute) {
                if (in_array($attribute, ['closing_date', 'etd', 'eta'])) $record->$attribute = date('d M', strtotime($record->$attribute));
                $tables_html .= "<td>{$record->$attribute}</td>";
            }
            $tables_html .= "</tr>";
        }

        $tables_html .= "</table>";
    }

    wp_register_script('gateway-cl', plugin_dir_url(__FILE__) . 'gateway-cl.js', ['jquery'], 1);
    wp_enqueue_script('gateway-cl');

    return "
        <table width=\"100%\" class=\"gatewaycl-export-schedule\">
            <tr class=\"gatewaycl-export-schedule-form\">
                <td>
                    <select name=\"origin_name\">
                        <option value=\"\">DEPARTURE</option>
                    </select>
                    <select name=\"destination_name\">
                        <option value=\"\">DESTINATION</option>
                    </select>
                    <select name=\"etd\">
                        <option value=\"\">ETD</option>
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
                        <select name=\"departure_from\">
                            <option value=\"\">DEPARTURE</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr class=\"gatewaycl-export-schedule-static-message\">
                <td>
                    The estimated schedule is considered for information purpose only and the Carrier may update, revise this schedule from time to time without any prior notice.
                </td>
            </tr>
            <tr class=\"gatewaycl-export-schedule-tables\">
                <td>{$tables_html}</td>
            </tr>
        </table>
    ";
});

function gatewaycl_get_import_schedule_direct()
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://gateway-cl.com/api/schedule_import?X-API-KEY=gateway-fms");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $json = curl_exec($ch);
    curl_close($ch);
    $schedule = json_decode($json);

    return $schedule->data[0]->direct;
}

add_shortcode('gatewaycl-import-schedule', function () {
    wp_register_style('gateway-cl', plugin_dir_url(__FILE__) . 'gateway-cl.css', [], 3);
    wp_enqueue_style('gateway-cl');

    $month_year = date('F Y', time());
    $tables_direct = [];
    $tables_html = '';
    $direct = gatewaycl_get_import_schedule_direct();

    foreach (array_values(array_unique(array_map(function ($record) {
        return $record->origin_name;
    }, $direct))) as $table_title) {
        $tables_direct[$table_title] = array_values(array_filter($direct, function ($record) use ($table_title) {
            return $table_title == $record->origin_name;
        }));
    }
    foreach ($tables_direct as $table_title => $records) {
        $tables_html .= "<table class=\"table-direct\">";
        $tables_html .= "<tr><td colspan=\"5\" class=\"table-title\">{$table_title}</td></tr>";

        $tables_html .= "<tr class=\"column-name\">";
        foreach (['VESSEL', 'VOY', 'CUT OFF CFS	', 'ETD', 'ETA'] as $thead) {
            $tables_html .= "<td>{$thead}</td>";
        }
        $tables_html .= "</tr>";

        foreach ($records as $record) {
            $tables_html .= "<tr class=\"data\" data-origin_name = \"$record->origin_name\" data-region_id = \"$record->region_id\" data-eta = \"$record->eta\">";
            foreach (['vessel', 'voyage', 'closing_date', 'etd', 'eta'] as $attribute) {
                if (in_array($attribute, ['closing_date', 'etd', 'eta'])) $record->$attribute = date('d M', strtotime($record->$attribute));
                $tables_html .= "<td>{$record->$attribute}</td>";
            }
            $tables_html .= "</tr>";
        }

        $tables_html .= "</table>";
    }

    wp_register_script('gateway-cl', plugin_dir_url(__FILE__) . 'gateway-cl.js', ['jquery'], 1);
    wp_enqueue_script('gateway-cl');
    if (isset($_POST['search'])) {
        wp_localize_script(
            'gateway-cl',
            'gateway_cl',
            array(
                'post' => $_POST,
            )
        );
    }

    return "
        <table width=\"100%\" class=\"gatewaycl-import-schedule\">
            <tr class=\"gatewaycl-import-schedule-form\">
                <td>
                    <select name=\"origin_name\">
                        <option value=\"\">Please Select POL</option>
                    </select>
                    <select name=\"region_id\">
                        <option value=\"\">Please Select POD</option>
                    </select>
                    <select name=\"eta\">
                        <option value=\"\">Select ETA</option>
                    </select>
                    <input type=\"submit\" name=\"search\" value=\"Search\">
                    <input type=\"reset\" value=\"Reset\">
                </td>
            </tr>
            <tr class=\"gatewaycl-import-schedule-month-year\">
                <td><h3>{$month_year}</h3></td>
            </tr>
            <tr class=\"gatewaycl-import-schedule-departure\">
                <td>
                    <div>
                        Port of Destination
                        <select name=\"port_of_destination\">
                            <option value=\"\">Select POD</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr class=\"gatewaycl-import-schedule-static-message\">
                <td>
                    The estimated schedule is considered for information purpose only and the Carrier may update, revise this schedule from time to time without any prior notice.
                </td>
            </tr>
            <tr class=\"gatewaycl-import-schedule-tables\">
                <td>{$tables_html}</td>
            </tr>
        </table>
    ";
});

add_shortcode('gatewaycl-import-schedule-widget', function () {
    wp_register_style('gateway-cl', plugin_dir_url(__FILE__) . 'gateway-cl.css', [], 3);
    wp_enqueue_style('gateway-cl');

    $data = array_map(function ($rec) {
        return [
            'origin_name' => $rec->origin_name,
            'region_id' => $rec->region_id,
            'eta' => $rec->eta
        ];
    }, gatewaycl_get_import_schedule_direct());
    wp_register_script('gateway-cl', plugin_dir_url(__FILE__) . 'gateway-cl.js', ['jquery'], 1);
    wp_enqueue_script('gateway-cl');
    wp_localize_script(
        'gateway-cl',
        'gateway_cl',
        array(
            'data' => $data,
        )
    );

    $tracking_import_shcedule_page_url = GATEWAY_CL_IMPORT_SCHEDULE_PAGE;
    $month_year = date('F Y', time());
    return "
        <table width=\"100%\" id=\"gatewaycl_widget_import_schedule\">
            <tr>
                <td><h2>Monthly Schedule</h2></td>
            </tr>
            <tr>
                <td><h3>{$month_year}<h3></td>
            </tr>
            <tr>
                <td>
                    <form method=\"POST\" action=\"{$tracking_import_shcedule_page_url}\">
                        <select name=\"origin_name\">
                            <option value=\"\">Please Select POL</option>
                        </select>
                        <select name=\"region_id\">
                            <option value=\"\">Please Select POD</option>
                        </select>
                        <select name=\"eta\">
                            <option value=\"\">Select ETA</option>
                        </select>
                        <input type=\"submit\" name=\"search\" value=\"Search\">
                        <input type=\"reset\" value=\"Reset\">
                    </form>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
        </table>
    ";
});

/* EXPORT */
const gatewaycl_export_schedule = jQuery(`.gatewaycl-export-schedule`)
let gatewaycl_export_schedule_data = []
const gatewaycl_export_schedule_dropdowns = {
    origin_name: gatewaycl_export_schedule.find(`select[name="origin_name"]`),
    destination_name: gatewaycl_export_schedule.find(`select[name="destination_name"]`),
    etd: gatewaycl_export_schedule.find(`select[name="etd"]`),
    departure_from: gatewaycl_export_schedule.find(`select[name="departure_from"]`),
}
const gatewaycl_export_schedule_tables = gatewaycl_export_schedule.find(`.gatewaycl-export-schedule-tables td table`)
const gatewaycl_export_schedule_rows = gatewaycl_export_schedule.find(`tr.data`)

gatewaycl_export_schedule_rows.each(function () {
    const row = jQuery(this)
    gatewaycl_export_schedule_data.push({
        origin_name: row.attr(`data-origin_name`),
        destination_name: row.attr(`data-destination_name`),
        etd: row.attr(`data-etd`)
    })
})

const export_origin_names = gatewaycl_export_schedule_data
    .map(row => {
        return row.origin_name
    })
    .filter((v, i, a) => a.indexOf(v) == i)
    .map(opt => {
        return `<option value="${opt}">${opt}</option>`
    })

gatewaycl_export_schedule_dropdowns.origin_name
    .append(export_origin_names)
    .change(e => {
        const selected = gatewaycl_export_schedule_dropdowns.origin_name.val()
        const destination_names = `` == selected ? [] : gatewaycl_export_schedule_data
            .filter(data => {
                return selected == data.origin_name
            })
            .map(data => {
                return data.destination_name
            })
            .filter((v, i, a) => a.indexOf(v) == i)
            .map(opt => {
                return `<option value="${opt}">${opt}</option>`
            })
        gatewaycl_export_schedule_dropdowns.destination_name
            .html([`<option value="">DESTINATION</option>`, ...destination_names])
            .change()
    })

gatewaycl_export_schedule_dropdowns.destination_name
    .change(function () {
        const etds = gatewaycl_export_schedule_data
            .filter(data => {
                return data.origin_name == gatewaycl_export_schedule_dropdowns.origin_name.val()
                    && data.destination_name == gatewaycl_export_schedule_dropdowns.destination_name.val()
            })
            .map(data => {
                return data.etd
            })
            .filter((v, i, a) => a.indexOf(v) == i)
            .map(opt => {
                return `<option value="${opt}">${opt}</option>`
            })
        gatewaycl_export_schedule_dropdowns.etd
            .html([`<option value="">ETD</option>`, ...etds])
    })

gatewaycl_export_schedule_dropdowns.departure_from
    .append(export_origin_names)
    .change(e => {
        const selected = gatewaycl_export_schedule_dropdowns.departure_from.val()
        if (`` == selected) return false;

        gatewaycl_export_schedule_rows.hide()
        jQuery(`tr.data[data-origin_name="${selected}"]`).show()
    })

gatewaycl_export_schedule.find(`[name="search"]`).click(e => {
    gatewaycl_export_schedule_rows.show()
    gatewaycl_export_schedule_tables.show()
    const filter = {
        origin_name: gatewaycl_export_schedule_dropdowns.origin_name.val(),
        destination_name: gatewaycl_export_schedule_dropdowns.destination_name.val(),
        etd: gatewaycl_export_schedule_dropdowns.etd.val()
    }

    gatewaycl_export_schedule_tables.hide()
    gatewaycl_export_schedule_tables.has(`td.table-title:contains("${filter.destination_name}")`).show()

    if (`` != filter.etd) {
        gatewaycl_export_schedule_rows.hide()
        jQuery(`tr.data[data-etd="${filter.etd}"]`).show()
    }
})

gatewaycl_export_schedule.find(`[type="reset"]`).click(e => {
    gatewaycl_export_schedule_dropdowns.origin_name.find(`option`).eq(0).attr(`selected`, true).change()
    gatewaycl_export_schedule_rows.show()
    gatewaycl_export_schedule_tables.show()
})

/* IMPORT */
const gatewaycl_import_schedule = jQuery(`.gatewaycl-import-schedule`)
let gatewaycl_import_schedule_data = []
const gatewaycl_import_schedule_dropdowns = {
    origin_name: gatewaycl_import_schedule.find(`select[name="origin_name"]`),
    region_id: gatewaycl_import_schedule.find(`select[name="region_id"]`),
    eta: gatewaycl_import_schedule.find(`select[name="eta"]`),
    port_of_destination: gatewaycl_import_schedule.find(`select[name="port_of_destination"]`),
}
const gatewaycl_import_schedule_tables = gatewaycl_import_schedule.find(`.gatewaycl-import-schedule-tables td table`)
const gatewaycl_import_schedule_rows = gatewaycl_import_schedule.find(`tr.data`)

gatewaycl_import_schedule_rows.each(function () {
    const row = jQuery(this)
    gatewaycl_import_schedule_data.push({
        origin_name: row.attr(`data-origin_name`),
        region_id: row.attr(`data-region_id`),
        eta: row.attr(`data-eta`)
    })
})

const import_origin_names = gatewaycl_import_schedule_data
    .map(row => {
        return row.origin_name
    })
    .filter((v, i, a) => a.indexOf(v) == i)
    .map(opt => {
        return `<option value="${opt}">${opt}</option>`
    })
gatewaycl_import_schedule_dropdowns.origin_name
    .append(import_origin_names)
    .change(import_update_dropdown_eta)

const region_ids = gatewaycl_import_schedule_data
    .map(data => {
        return data.region_id
    })
    .filter((v, i, a) => a.indexOf(v) == i)
    .map(opt => {
        return `<option value="${opt}">${opt}</option>`
    })
gatewaycl_import_schedule_dropdowns.region_id
    .append(region_ids)
    .change(import_update_dropdown_eta)

function import_update_dropdown_eta() {
    const etas = gatewaycl_import_schedule_data
        .filter(data => {
            return data.origin_name == gatewaycl_import_schedule_dropdowns.origin_name.val()
            // && data.region_id == gatewaycl_import_schedule_dropdowns.region_id.val()
        })
        .map(data => {
            return data.eta
        })
        .filter((v, i, a) => a.indexOf(v) == i)
        .map(opt => {
            return `<option value="${opt}">${opt}</option>`
        })
    gatewaycl_import_schedule_dropdowns.eta
        .html([`<option value="">Select ETA</option>`, ...etas])
}

gatewaycl_import_schedule_dropdowns.port_of_destination
    .append(region_ids)
    .change(e => {
        const selected = gatewaycl_import_schedule_dropdowns.port_of_destination.val()
        if (`` == selected) return false;

        gatewaycl_import_schedule_rows.hide()
        jQuery(`tr.data[data-region_id="${selected}"]`).show()
    })

gatewaycl_import_schedule.find(`[name="search"]`).click(e => {
    gatewaycl_import_schedule_rows.show()
    gatewaycl_import_schedule_tables.show()
    const filter = {
        origin_name: gatewaycl_import_schedule_dropdowns.origin_name.val(),
        region_id: gatewaycl_import_schedule_dropdowns.region_id.val(),
        eta: gatewaycl_import_schedule_dropdowns.eta.val()
    }

    gatewaycl_import_schedule_tables.hide()
    gatewaycl_import_schedule_tables.has(`td.table-title:contains("${filter.origin_name}")`).show()

    if (`` != filter.eta) {
        gatewaycl_import_schedule_rows.hide()
        jQuery(`tr.data[data-eta="${filter.eta}"]`).show()
    }
})

gatewaycl_import_schedule.find(`[type="reset"]`).click(e => {
    gatewaycl_import_schedule.find(`select`).each(function () {
        jQuery(this).val(``)
        jQuery(this).find(`option`).eq(0).attr(`selected`, true).change()
    })
    gatewaycl_import_schedule_rows.show()
    gatewaycl_import_schedule_tables.show()
})

if (0 < gatewaycl_import_schedule.length) {
    gatewaycl_import_schedule_dropdowns.origin_name.val(gateway_cl.post.origin_name).change()
    gatewaycl_import_schedule_dropdowns.region_id.val(gateway_cl.post.region_id)
    gatewaycl_import_schedule_dropdowns.eta.val(gateway_cl.post.eta)
    gatewaycl_import_schedule.find(`[name="search"]`).click()
}

/* WIDGET IMPORT SCHEDULE */
const gatewaycl_widget_import_schedule = jQuery(`#gatewaycl_widget_import_schedule`)
if (0 < gatewaycl_widget_import_schedule.length) {
    const gatewaycl_widget_import_schedule_dropdowns = {
        origin_name: gatewaycl_widget_import_schedule.find(`select[name="origin_name"]`),
        region_id: gatewaycl_widget_import_schedule.find(`select[name="region_id"]`),
        eta: gatewaycl_widget_import_schedule.find(`select[name="eta"]`),
    }
    const widget_import_import_origin_names = gateway_cl.data
        .map(row => {
            return row.origin_name
        })
        .filter((v, i, a) => a.indexOf(v) == i)
        .map(opt => {
            return `<option value="${opt}">${opt}</option>`
        })
    gatewaycl_widget_import_schedule_dropdowns.origin_name
        .append(widget_import_import_origin_names)
        .change(import_update_dropdown_eta)

    const widget_import_region_ids = gateway_cl.data
        .map(data => {
            return data.region_id
        })
        .filter((v, i, a) => a.indexOf(v) == i)
        .map(opt => {
            return `<option value="${opt}">${opt}</option>`
        })
    gatewaycl_widget_import_schedule_dropdowns.region_id
        .append(widget_import_region_ids)
        .change(import_update_dropdown_eta)

    function import_update_dropdown_eta() {
        const widget_import_etas = gateway_cl.data
            .filter(data => {
                return data.origin_name == gatewaycl_widget_import_schedule_dropdowns.origin_name.val()
                // && data.region_id == gatewaycl_widget_import_schedule_dropdowns.region_id.val()
            })
            .map(data => {
                return data.eta
            })
            .filter((v, i, a) => a.indexOf(v) == i)
            .map(opt => {
                return `<option value="${opt}">${opt}</option>`
            })
        gatewaycl_widget_import_schedule_dropdowns.eta
            .html([`<option value="">Select ETA</option>`, ...widget_import_etas])
    }
}
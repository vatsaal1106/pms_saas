<?php

namespace App\DataTables;

use App\Models\PurposeConsentLead;
use Carbon\Carbon;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class LeadGDPRDataTable extends BaseDataTable
{

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('status', function ($row) {
                if ($row->status == 'agree') {
                    $status = __('modules.gdpr.optIn');
                }
                else if ($row->status == 'disagree') {
                    $status = __('modules.gdpr.optOut');
                }
                else {
                    $status = '';
                }

                return $status;
            })
            ->editColumn(
                'created_at',
                function ($row) {
                    return Carbon::parse($row->created_at)->translatedFormat($this->company->date_format);
                }
            )
            ->editColumn(
                'additional_description',
                function ($row) {
                    return $row->additional_description ? $row->additional_description : '--';
                }
            )
            ->editColumn(
                'action',
                function ($row) {
                    return $row->status;
                }
            )
            ->rawColumns(['status']);
    }

    /**
     * @param PurposeConsentLead $model
     * @return \Illuminate\Database\Query\Builder
     */
    public function query(PurposeConsentLead $model)
    {
        $request = $this->request();

        return $model->select('purpose_consent.name', 'purpose_consent_leads.created_at', 'purpose_consent_leads.status', 'purpose_consent_leads.ip', 'users.name as username', 'purpose_consent_leads.additional_description')
            ->join('purpose_consent', 'purpose_consent.id', '=', 'purpose_consent_leads.purpose_consent_id')
            ->leftJoin('users', 'purpose_consent_leads.updated_by_id', '=', 'users.id')
            ->where('purpose_consent_leads.lead_id', $request->leadID);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('leads-gdpr-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["leads-gdpr-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    });
                    $(".statusChange").selectpicker();
                }',
            ]);

        if (canDataTableExport()) {
            $dataTable->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
        }

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('modules.gdpr.purpose') => ['data' => 'name', 'name' => 'purpose_consent.name', 'title' => __('modules.gdpr.purpose')],
            __('app.date') => ['data' => 'created_at', 'name' => 'purpose_consent.created_at', 'title' => __('app.date')],
            __('modules.gdpr.ipAddress') => ['data' => 'ip', 'name' => 'purpose_consent.ip', 'title' => __('modules.gdpr.ipAddress')],
            __('modules.gdpr.additionalDescription') => ['data' => 'additional_description', 'name' => 'additional_description', 'title' => __('modules.gdpr.additionalDescription')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(150)
                ->addClass('text-right pr-20')
        ];
    }

}

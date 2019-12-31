<?php

namespace App\Ninja\Datatables;

use Auth;
use URL;
use Utils;

class ProjectPositionDatatable extends TaskDatatable
{
    public $sortCol = 1;

    public function columns()
    {
        return [
            [
                'client_name',
                function ($model) {
                    if (Auth::user()->can('view', [ENTITY_CLIENT, $model]))
                        return $model->client_public_id ? link_to("clients/{$model->client_public_id}", Utils::getClientDisplayName($model))->toHtml() : '';
                    else
                        return Utils::getClientDisplayName($model);

                },
                ! $this->hideClient,
            ],
            [
                'description',
                function ($model) {
                    if (Auth::user()->can('view', [ENTITY_EXPENSE, $model]))
                        return link_to("tasks/{$model->public_id}/edit", $this->showWithTooltip($model->description))->toHtml();
                    else
                        $this->showWithTooltip($model->description);
                },
            ],
            [
                'service_period',
                function ($model) {
                    return $model->service_period;
                },
            ],
            [
                'amount',
                function ($model) {
                    return Utils::formatMoney($model->amount);
                },
            ],
            [
                'status',
                function ($model) {
                    return self::getStatusLabel($model);
                },
            ],
        ];
    }

    public function actions()
    {
        return [
            [
                trans('texts.edit_task'),
                function ($model) {
                    return URL::to('tasks/'.$model->public_id.'/edit');
                },
                function ($model) {
                    return (! $model->deleted_at || $model->deleted_at == '0000-00-00') && Auth::user()->can('view', [ENTITY_TASK, $model]);
                },
            ],
            [
                trans('texts.view_invoice'),
                function ($model) {
                    return URL::to("/invoices/{$model->invoice_public_id}/edit");
                },
                function ($model) {
                    return $model->invoice_number && Auth::user()->can('view', [ENTITY_TASK, $model]);
                },
            ],
            [
                trans('texts.invoice_task'),
                function ($model) {
                    return "javascript:submitForm_task('invoice', {$model->public_id})";
                },
                function ($model) {
                    return ! $model->is_running && ! $model->invoice_number && (! $model->deleted_at || $model->deleted_at == '0000-00-00') && Auth::user()->canCreateOrEdit(ENTITY_INVOICE);
                },
            ],
        ];
    }
}

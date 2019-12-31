<?php

namespace App\Ninja\Datatables;

use Auth;
use URL;
use Utils;

class ConsultingProjectDatatable extends ProjectDatatable
{
    public function columns()
    {
        return [
            [
                'project',
                function ($model) {
                    if (Auth::user()->can('view', [ENTITY_PROJECT, $model]))
                        return $this->addNote(link_to("projects/{$model->public_id}", $model->project)->toHtml(), $model->private_notes);
                    else
                        return $model->project;


                },
            ],
            [
                'client_name',
                function ($model) {
                    if ($model->client_public_id) {
                        if (Auth::user()->can('view', [ENTITY_CLIENT, $model]))
                            return link_to("clients/{$model->client_public_id}", $model->client_name)->toHtml();
                        else
                            return Utils::getClientDisplayName($model);

                    } else {
                        return '';
                    }
                },
            ],
            [
                'assoc_client_name',
                function ($model) {
                    if ($model->assoc_client_name) {
                        if (Auth::user()->can('view', [ENTITY_CLIENT, $model]))
                            return link_to("clients/{$model->assoc_client_public_id}", $model->assoc_client_name)->toHtml();
                        else
                            return $model->assoc_client_name;

                    } else {
                        return '';
                    }
                },
            ],
            [
                'candidate_position',
                function ($model) {
                    return $model->candidate_position ?: '';
                },
            ],
            [
                'candidate_name',
                function ($model) {
                    return $model->candidate_name ?: '';
                },
            ],
            [
                'warranty_period_until',
                function ($model) {
                    return Utils::toSqlDate($model->warranty_period_until);
                }
            ],
        ];
    }

    public function actions()
    {
        return [
            [
                trans('texts.edit_project'),
                function ($model) {
                    return URL::to("projects/{$model->public_id}/edit");
                },
                function ($model) {
                    return Auth::user()->can('view', [ENTITY_PROJECT, $model]);
                },
            ],
        ];
    }
}

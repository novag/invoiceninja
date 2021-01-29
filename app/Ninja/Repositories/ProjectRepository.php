<?php

namespace App\Ninja\Repositories;

use App\Models\Project;
use Auth;
use DB;
use Utils;

class ProjectRepository extends BaseRepository
{
    public function getClassName()
    {
        return 'App\Models\Project';
    }

    public function all()
    {
        return Project::scope()->get();
    }

    public function find($filter = null, $userId = false)
    {
        $query = DB::table('projects')
                ->where('projects.account_id', '=', Auth::user()->account_id)
                ->leftjoin('clients', 'clients.id', '=', 'projects.client_id')
                ->leftjoin('clients AS assoc_clients', 'assoc_clients.id', '=', 'projects.assoc_client_id')
                ->leftJoin('contacts', 'contacts.client_id', '=', 'clients.id')
                ->where('contacts.deleted_at', '=', null)
                ->where('clients.deleted_at', '=', null)
                ->where('assoc_clients.deleted_at', '=', null)
                ->where(function ($query) { // handle when client isn't set
                    $query->where('contacts.is_primary', '=', true)
                          ->orWhere('contacts.is_primary', '=', null);
                })
                ->select(
                    'projects.name as project',
                    'projects.public_id',
                    'projects.user_id',
                    'projects.deleted_at',
                    'projects.task_rate',
                    'projects.is_deleted',
                    'projects.due_date',
                    'projects.budgeted_hours',
                    'projects.private_notes',
                    'projects.candidate_position',
                    'projects.annual_target_salary',
                    'projects.fee_rate',
                    'projects.expense_rate',
                    'projects.candidate_name',
                    'projects.signed_at',
                    'projects.start_of_work',
                    'projects.warranty_period_until',
                    DB::raw("COALESCE(NULLIF(clients.name,''), NULLIF(CONCAT(contacts.first_name, ' ', contacts.last_name),''), NULLIF(contacts.email,'')) client_name"),
                    'clients.user_id as client_user_id',
                    'clients.public_id as client_public_id',
                    'assoc_clients.name AS assoc_client_name',
                    'assoc_clients.user_id AS assoc_client_user_id',
                    'assoc_clients.public_id AS assoc_client_public_id'
                );

        $this->applyFilters($query, ENTITY_PROJECT);

        if ($filter) {
            $query->where(function ($query) use ($filter) {
                $query->where('clients.name', 'like', '%'.$filter.'%')
                      ->orWhere('contacts.first_name', 'like', '%'.$filter.'%')
                      ->orWhere('contacts.last_name', 'like', '%'.$filter.'%')
                      ->orWhere('contacts.email', 'like', '%'.$filter.'%')
                      ->orWhere('projects.name', 'like', '%'.$filter.'%');
            });
        }

        if ($userId) {
            $query->where('projects.user_id', '=', $userId);
        }

        return $query;
    }

    public function save($input, $project = false)
    {
        $publicId = isset($data['public_id']) ? $data['public_id'] : false;

        if (! $project) {
            $project = Project::createNew();
            $project['client_id'] = $input['client_id'];

            if (Auth::user()->account->consulting_mode) {
                $project['assoc_client_id'] = $input['assoc_client_id'];
            }
        }

        $project->fill($input);

        if (isset($input['annual_target_salary'])) {
            $project->annual_target_salary = Utils::parseFloat($input['annual_target_salary']);
        }

        if (isset($input['due_date'])) {
            $project->due_date = Utils::toSqlDate($input['due_date']);
        }

        if (Auth::user()->account->consulting_mode) {
            if (isset($input['signed_at'])) {
                $project->signed_at = Utils::toSqlDate($input['signed_at']);
            }

            if (isset($input['start_of_work'])) {
                $project->start_of_work = Utils::toSqlDate($input['start_of_work']);
            }

            if (isset($input['warranty_period_until'])) {
                $project->warranty_period_until = Utils::toSqlDate($input['warranty_period_until']);
            }
        }

        $project->save();

        return $project;
    }
}

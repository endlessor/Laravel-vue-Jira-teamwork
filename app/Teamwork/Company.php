<?php

namespace App\Teamwork;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'teamwork_companies';

    /**
     * @param $companyData
     * @return Company
     */
    public static function syncFromData($companyData)
    {
        $company = self::where('teamwork_id', $companyData['id'])->first();
        if (!$company) {
            $company = new Company();
            $company->teamwork_id = $companyData['id'];
        }

        $company->name = $companyData['name'];
        $company->save();

        return $company;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function apps()
    {
        return $this->belongsToMany(App::class, 'teamwork_company_app')->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}

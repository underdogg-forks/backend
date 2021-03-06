<?php

namespace plunner;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;

/**
 * Class Employee
 *
 * @author Claudio Cardinale <cardi@thecsea.it>
 * @copyright 2015 Claudio Cardinale
 * @version 1.0.0
 * @package plunner
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property integer $company_id
 * @property string $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read mixed $is_planner
 * @property-read \plunner\Company $company
 * @property-read \Illuminate\Database\Eloquent\Collection|\plunner\Group[] $groups
 * @property-read \Illuminate\Database\Eloquent\Collection|\plunner\Calendar[] $calendars
 * @property-read \Illuminate\Database\Eloquent\Collection|\plunner\Meeting[] $meetings
 */
class Employee extends Model implements AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract,
    PolicyCheckable
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'pivot'];

    /**
     * @var array
     */
    protected $appends = ['is_planner'];

    public function getIsPlannerAttribute()
    {
        return !($this->groupsManagedRelationship()->get()->isEmpty());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    protected function groupsManagedRelationship()
    {
        return $this->HasMany(Group::class, 'planner_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('plunner\Company');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function calendars()
    {
        return $this->hasMany('plunner\Calendar');
    }

    /**
     * Get the e-mail address where password reset links are sent.
     * This is needed for multiple user type login
     *
     * Make email unique
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        list(, $caller) = debug_backtrace(false);
        if (isset($caller['class']))
            $caller = explode('\\', $caller['class']);
        else
            $caller = '';

        //check if this function is called by email sender
        if ((count($caller) && $caller[count($caller) - 1] == 'PasswordBroker') || (defined('HHVM_VERSION') && $caller == ''))
            return $this->email;
        //return unique identify for token repository
        return $this->email . $this->company->id;
    }

    /**
     * @param Group $group
     * @return bool
     */
    public function verifyGroup(Group $group)
    {
        return $this->belongsToGroup($group);
    }

    /**
     * @param Group $group
     * @return bool
     */
    public function belongsToGroup(Group $group)
    {
        $group = $this->groups()->where('id', $group->id)->first();
        return (is_object($group) && $group->exists);
    }

    /*
     * for a normal employee the policyCheckable methods say if the employee can se or not the element
     */

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany('plunner\Group', 'employee_group', 'employee_id'); //needed for planner model
    }

    /**
     * @param Employee $employee
     * @return bool
     */
    public function verifyEmployee(Employee $employee)
    {
        return $employee->company_id === $this->company_id;
    }

    /**
     * @param Company $company
     * @return bool
     */
    public function verifyCompany(Company $company)
    {
        return $company->id === $this->company_id;
    }

    /**
     * the employee can modify a calendar
     * @param Calendar $calendar
     * @return bool
     */
    public function verifyCalendar(Calendar $calendar)
    {
        //TODO test this
        return $calendar->employee_id == $this->id;
    }

    /**
     * @param Timeslot $timeslot
     * @return bool
     */
    public function verifyTimeslot(Timeslot $timeslot)
    {
        //TODO test this
        return $timeslot->calendar->employee_id == $this->id;
    }

    /**
     * @param Meeting $meeting
     * @return bool
     */
    public function verifyMeeting(Meeting $meeting)
    {
        //TODO test this
        $meeting = $this->meetings()->whereId($meeting->id)->first();
        return (is_object($meeting) && $meeting->exists);
    }

    /**
     * meetings where the user participates
     * to get all meetings where the user can go user groups with meetings
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function meetings()
    {
        //TODO durign the inserting chek if the meeting is of a group of the user
        return $this->belongsToMany(Meeting::class);
    }

    /**
     * @param MeetingTimeslot $meetingTimeslot
     * @return bool
     */
    public function verifyMeetingTimeslot(MeetingTimeslot $meetingTimeslot)
    {
        //TODO implement and test this
        return false;
    }
}

<?php

namespace plunner;


/**
 * Class Planner
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
class Planner extends Employee
{

    /**
     * @param Group $group
     * @return bool
     */
    public function verifyGroup(Group $group)
    {
        $group = $this->groupsManaged()->where('id', $group->id)->first();

        return (is_object($group) && $group->exists);
    }

    /*
    * for a planer employee the policyCheckable methods say if the planer can modify or not that part
    */

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupsManaged()
    {
        return $this->groupsManagedRelationship();
    }

    /**
     * @param Employee $employee
     * @return bool
     */
    public function verifyEmployee(Employee $employee)
    {
        $group = $this->groupsManaged()->whereHas('employees', function ($query) use ($employee) {
            $query->where('employees.id', $employee->id);
        })->first();

        return (is_object($group) && $group->exists);
    }

    /**
     * @param Company $company
     * @return bool
     */
    public function verifyCompany(Company $company)
    {
        return false;
    }

    /**
     * the employee can see a calendar
     * @param Calendar $calendar
     * @return bool
     */
    public function verifyCalendar(Calendar $calendar)
    {
        //TODO implement and test
        return false;
    }

    /**
     * @param Timeslot $timeslot
     * @return bool
     */
    public function verifyTimeslot(Timeslot $timeslot)
    {
        //TODO implement and test this
        return false;
    }

    /**
     * @param MeetingTimeslot $meetingTimeslot
     * @return bool
     */
    public function verifyMeetingTimeslot(MeetingTimeslot $meetingTimeslot)
    {
        //TODO test this
        return $this->verifyMeeting($meetingTimeslot->meeting);
    }

    /**
     * @param Meeting $meeting
     * @return bool
     */
    public function verifyMeeting(Meeting $meeting)
    {
        //TODO test this
        return $meeting->group->planner_id == $this->id;
    }
}

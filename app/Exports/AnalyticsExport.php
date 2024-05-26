<?php

namespace App\Exports;

use App\Models\Election;
use App\Models\StudentOrganization;
use App\Models\Candidates;
use App\Models\PartyList;
use App\Models\Eligibles;
use App\Models\VotingsTracker;
use App\Models\Course;
use App\Models\CoC;
use App\Models\Student;
use App\Models\Metadata;
use App\Models\StudentClass;
use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class AnalyticsExport implements FromCollection, WithCustomStartCell, WithStrictNullComparison, WithEvents
{
    protected $id;
    protected $election_name;

    public function __construct($id, $election_name)
    {
        $this->id = $id;
        $this->election_name = $election_name;
    }

    public function getStudentMetadataByStudNumber($studentNumber)
    {
        $student = Student::where('StudentNumber', $studentNumber)->first();

        if (!$student) {
            return ["error" => "Student with student number {$studentNumber} not found."];
        }

        $studentMetadata = Metadata::join('SPSClass', 'SPSMetadata.MetadataId', '=', 'SPSClass.MetadataId')
            ->join('SPSStudentClassGrade', 'SPSClass.ClassId', '=', 'SPSStudentClassGrade.ClassId')
            ->where('SPSStudentClassGrade.StudentId', $student->StudentId)
            ->first();

        if (!$studentMetadata) {
            return ["error" => "No metadata found for student with student number {$studentNumber}."];
        }

        $studentCourse = Course::where('CourseId', $studentMetadata->CourseId)->first();

        if (!$studentCourse) {
            return ["error" => "No course found for student with student number {$studentNumber}."];
        }

        return [
            "MetadataId" => $studentMetadata->MetadataId,
            "CourseId" => $studentMetadata->CourseId,
            "CourseCode" => $studentCourse ? $studentCourse->CourseCode : '',
            "Year" => $studentMetadata->Year,
            "Semester" => $studentMetadata->Semester,
            "Batch" => $studentMetadata->Batch,
            "created_at" => $studentMetadata->created_at,
            "updated_at" => $studentMetadata->updated_at
        ];
    }

    public function getStudentSectionByStudNumber($studentNumber)
    {
        $student = Student::where('StudentNumber', $studentNumber)->first();

        if (!$student) {
            return ["error" => "Student with student number {$studentNumber} not found."];
        }

        $studentSection = StudentClass::join('SPSStudentClassGrade', 'SPSClass.ClassId', '=', 'SPSStudentClassGrade.ClassId')
            ->where('SPSStudentClassGrade.StudentId', $student->StudentId)
            ->first();

        if (!$studentSection) {
            return false;
        }

        return $studentSection->Section;
    }

    public function collection()
    {
        $id = $this->id;
        $election = Election::where('ElectionId', $id)->first();
        $election_data = [];

        $student_organization = StudentOrganization::where('StudentOrganizationId', $election->StudentOrganizationId)->first();

        $election_data["StudentOrganizationLogo"] = $student_organization->OrganizationLogo;
        $election_data['StudentOrganizationName'] = $student_organization->OrganizationName;
        $election_data['ElectionName'] = $election->ElectionName;
        $election_data['Semester'] = $election->Semester;
        $election_data['SchoolYear'] = $election->SchoolYear;
        $election_data['CourseRequirement'] = $student_organization->OrganizationMemberRequirements;

        $now = Carbon::now('Asia/Manila');
        if ($now < new Carbon($election->CoCFilingStart, 'Asia/Manila')) {
            $election_data["ElectionPeriod"] = "Pre-Election";
        } elseif ($now >= new Carbon($election->CoCFilingStart, 'Asia/Manila') && $now < new Carbon($election->CoCFilingEnd, 'Asia/Manila')) {
            $election_data["ElectionPeriod"] = "Filing Period";
        } elseif ($now >= new Carbon($election->CampaignStart, 'Asia/Manila') && $now < new Carbon($election->CampaignEnd, 'Asia/Manila')) {
            $election_data["ElectionPeriod"] = "Campaign Period";
        } elseif ($now >= new Carbon($election->VotingStart, 'Asia/Manila') && $now < new Carbon($election->VotingEnd, 'Asia/Manila')) {
            $election_data["ElectionPeriod"] = "Voting Period";
        } elseif ($now >= new Carbon($election->AppealStart, 'Asia/Manila') && $now < new Carbon($election->AppealEnd, 'Asia/Manila')) {
            $election_data["ElectionPeriod"] = "Appeal Period";
        } else {
            $election_data["ElectionPeriod"] = "Post-Election";
        }

        $num_candidates = Candidates::where('ElectionId', $id)->count();
        $election_data['NumberOfCandidates'] = $num_candidates;

        $num_partylists = PartyList::where('ElectionId', $id)->where('Status', 'Approved')->count();
        $election_data['NumberOfPartylists'] = $num_partylists;

        $num_voters = Eligibles::where('ElectionId', $id)->count();
        $election_data['NumberOfVoters'] = $num_voters;

        $active_voters = VotingsTracker::where('ElectionId', $id)->distinct('VoterStudentNumber')->count();
        $election_data['NumberOfActiveVoters'] = $active_voters;

        $inactive_voters = $num_voters - $active_voters;
        $election_data['NumberOfInactiveVoters'] = $inactive_voters;

        $course_distribution = [];
        $courses = Course::all();
        foreach ($courses as $course) {
            $course_distribution[$course->CourseCode] = 0;
        }

        $votes_per_course = VotingsTracker::where('ElectionId', $id)->distinct('VoterStudentNumber')->get();
        foreach ($votes_per_course as $vote) {
            $course = Course::where('CourseId', $vote->CourseId)->first();
            $course_distribution[$course->CourseCode] += 1;
        }

        $election_data['CourseDistribution'] = $course_distribution;

        $approved_coc = CoC::where('ElectionId', $id)->where('Status', 'Approved')->count();
        $election_data['NumberOfApprovedCoC'] = $approved_coc;

        $rejected_coc = CoC::where('ElectionId', $id)->where('Status', 'Rejected')->count();
        $election_data['NumberOfRejectedCoC'] = $rejected_coc;

        $approved_partylist = PartyList::where('ElectionId', $id)->where('Status', 'Approved')->count();
        $election_data['NumberOfApprovedPartylist'] = $approved_partylist;

        $rejected_partylist = PartyList::where('ElectionId', $id)->where('Status', 'Rejected')->count();
        $election_data['NumberOfRejectedPartylist'] = $rejected_partylist;

        // Candidates

        $cocs = CoC::where('ElectionId', $this->id)->where('Status', 'Approved')->get();
        $candidates_data = [];

        foreach ($cocs as $coc) {
            $coc_dict = [];

            $student = Student::where('StudentNumber', $coc->StudentNumber)->first();
            $coc_dict["CandidateFullName"] = $student->FirstName . " " . $student->MiddleName . " " . $student->LastName;

            $coc_dict["CandidateDisplayPhoto"] = $coc->DisplayPhoto;

            $coc_dict["CandidatePositionName"] = $coc->SelectedPositionName;

            $partylist = PartyList::where('ElectionId', $this->id)->where('PartyListId', $coc->PartyListId)->first();
            if ($partylist) {
                $coc_dict["CandidatePartyListName"] = $partylist->PartyListName;
            } else {
                $coc_dict["CandidatePartyListName"] = "Independent";
            }

            // Get candidate course, year and section
            // Assuming you have methods to get student metadata and section
            $get_student_metadata = $this->getStudentMetadataByStudNumber($coc->StudentNumber);
            $get_student_section = $this->getStudentSectionByStudNumber($coc->StudentNumber);

            $course = "";
            $year = "";
            $section = "";

            if (isset($get_student_metadata["CourseCode"])) {
                $course = $get_student_metadata["CourseCode"];
                $year = $get_student_metadata["Year"];
            }

            if ($get_student_section) {
                $section = $get_student_section;
            }

            $coc_dict["CandidateCourseYearSection"] = $course . " " . $year . "-" . $section;

            $coc_dict["CandidateMotto"] = $coc->Motto;

            $coc_dict["CandidatePlatform"] = $coc->Platform;

            $candidate = Candidates::where('ElectionId', $this->id)->where('StudentNumber', $coc->StudentNumber)->first();
            $coc_dict["CandidateVotes"] = $candidate->Votes;

            $coc_dict["CandidateAbstains"] = $candidate->TimesAbstained;

            $candidate_id = $candidate->CandidateId;

            $votes_per_course = VotingsTracker::where('ElectionId', $this->id)->where('VotedCandidateId', $candidate_id)->distinct('VoterStudentNumber')->get();

            $course_dict = [];
            $courses = Course::all();
            foreach ($courses as $course) {
                $course_dict[$course->CourseCode] = 0;
            }

            foreach ($votes_per_course as $vote) {
                $course = Course::where('CourseId', $vote->CourseId)->first();
                $course_code = $course->CourseCode;

                $course_dict[$course_code] += 1;
            }

            $coc_dict["CandidateVotesPerCourse"] = $course_dict;

            $coc_dict["CandidateOneStar"] = $candidate->OneStar;
            $coc_dict["CandidateTwoStar"] = $candidate->TwoStar;
            $coc_dict["CandidateThreeStar"] = $candidate->ThreeStar;
            $coc_dict["CandidateFourStar"] = $candidate->FourStar;
            $coc_dict["CandidateFiveStar"] = $candidate->FiveStar;

            $candidates_data[] = $coc_dict;
        }

        return collect([
            ['Election Analytics: ' . $this->election_name],
            [''],
            ['Semester'], 
            [$election_data['Semester']],
            [''],
            ['School Year'], 
            [$election_data['SchoolYear']],
            [''],
            ['Course Requirement'], 
            [$election_data['CourseRequirement']],
            [''],
            ['Number of Candidates'], 
            [$election_data['NumberOfCandidates']],
            [''],
            ['Number of Partylists'], 
            [$election_data['NumberOfPartylists']],
            [''],
            ['Voters Population'], 
            [$election_data['NumberOfVoters']],
            [''],
            ['Number of Active Voters'], 
            [$election_data['NumberOfActiveVoters']],
            [''],
            ['Number of Inactive Voters'], 
            [$election_data['NumberOfInactiveVoters']],
            [''],
            ['Voters Course Distribution'], 
            [''],
            ['Course Code', 'Number of Voters'],
            ...array_map(function($course, $count) {
                return [$course, $count];
            }, array_keys($election_data['CourseDistribution']), array_values($election_data['CourseDistribution'])),
            [''],
            ['Number of Approved CoC'], 
            [$election_data['NumberOfApprovedCoC']],
            [''],
            ['Number of Rejected CoC'], 
            [$election_data['NumberOfRejectedCoC']],
            [''],
            ['Number of Approved Partylist'], 
            [$election_data['NumberOfApprovedPartylist']],
            [''],
            ['Number of Rejected Partylist'], 
            [$election_data['NumberOfRejectedPartylist']],
            [''],
            [''],
            ['Candidates'],
            [''],
            ['Candidate Full Name', 'Candidate Position Name', 'Candidate Partylist Name', 'Candidate Course Year Section', 'Candidate Motto', 'Candidate Platform', 'Candidate Votes', 'Candidate Abstains', 'Candidate Votes Per Course', 'Candidate One Star', 'Candidate Two Star', 'Candidate Three Star', 'Candidate Four Star', 'Candidate Five Star'],
            ...array_map(function($candidate) {
                return [
                    $candidate['CandidateFullName'],
                    $candidate['CandidatePositionName'],
                    $candidate['CandidatePartyListName'],
                    $candidate['CandidateCourseYearSection'],
                    $candidate['CandidateMotto'],
                    $candidate['CandidatePlatform'],
                    $candidate['CandidateVotes'],
                    $candidate['CandidateAbstains'],
                    json_encode($candidate['CandidateVotesPerCourse']),
                    $candidate['CandidateOneStar'],
                    $candidate['CandidateTwoStar'],
                    $candidate['CandidateThreeStar'],
                    $candidate['CandidateFourStar'],
                    $candidate['CandidateFiveStar']
                ];
            }, $candidates_data)
        ]);
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $titleCell = 'A1';
                $event->sheet->getDelegate()->getStyle($titleCell)->getFont()->setBold(true)->setSize(16);

                // Auto size all columns
                foreach (range('A', 'Z') as $columnID) {
                    $event->sheet->getDelegate()->getColumnDimension($columnID)
                        ->setAutoSize(true);
                }

                // Left align all cells
                $event->sheet->getDelegate()->getStyle('A1:Z100')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            }
        ];

    }
}

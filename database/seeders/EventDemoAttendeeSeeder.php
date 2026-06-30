<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationAnswer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventDemoAttendeeSeeder extends Seeder
{
    private array $firstNames = [
        'Ahmad', 'Muhammad', 'Nurul', 'Siti', 'Mohd', 'Nor', 'Nur', 'Abdul',
        'Farah', 'Aisyah', 'Haziq', 'Izzati', 'Syafiq', 'Liyana', 'Amirul',
        'Hidayah', 'Hafiz', 'Nadhirah', 'Afiq', 'Zulaikha', 'Rizwan', 'Umairah',
        'Firdaus', 'Hanis', 'Danial', 'Nabilah', 'Aiman', 'Shazwani', 'Irfan',
        'Aliya', 'Harith', 'Fatihah', 'Zafran', 'Maisarah', 'Aqil', 'Adilah',
        'Kevin', 'Mei Ling', 'Wei Jie', 'Li Xin', 'Priya', 'Rajan', 'Kavitha',
        'Shankar', 'Jason', 'Michelle', 'David', 'Sarah', 'James', 'Emma',
    ];

    private array $lastNames = [
        'Abdullah', 'Ibrahim', 'Hassan', 'Rahman', 'Mohd Yusof', 'Ismail',
        'Mohd Noor', 'Razak', 'Hamid', 'Bakar', 'Aziz', 'Karim', 'Rahim',
        'Othman', 'Daud', 'Salleh', 'Ghani', 'Ariffin', 'Wahab', 'Zainudin',
        'Tan', 'Lim', 'Wong', 'Lee', 'Ng', 'Cheong', 'Yap', 'Chong', 'Chan',
        'Krishnan', 'Muthu', 'Pillai', 'Nair', 'Gopal', 'Rajah', 'Menon',
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Taylor', 'Davis',
    ];

    private array $organizations = [
        'Petronas', 'Maybank', 'CIMB Group', 'Telekom Malaysia', 'Axiata',
        'IHH Healthcare', 'RHB Bank', 'Public Bank', 'Sime Darby', 'IOI Group',
        'TNB', 'Maxis', 'Digi', 'AirAsia', 'Malaysia Airlines',
        'Ministry of Finance', 'Ministry of Education', 'MARA', 'EPF', 'SOCSO',
        'Universiti Malaya', 'UiTM', 'UTM', 'UPM', 'USM',
        'Deloitte Malaysia', 'KPMG Malaysia', 'PwC Malaysia', 'EY Malaysia',
        'Accenture Malaysia', 'IBM Malaysia', 'Dell Technologies', 'HP Malaysia',
        'Tech Mahindra', 'Infosys', 'TCS Malaysia', 'Grab', 'Shopee Malaysia',
        'Lazada Malaysia', 'FoodPanda', 'GoGet Malaysia', 'Carsome', 'PropertyGuru',
        'Freelancer', 'Self Employed', 'Startup Founder', 'Consultant',
    ];

    private array $designations = [
        'Software Engineer', 'Senior Developer', 'Product Manager', 'UX Designer',
        'Data Scientist', 'DevOps Engineer', 'System Analyst', 'IT Manager',
        'CTO', 'CEO', 'COO', 'Head of Engineering', 'VP of Technology',
        'Business Analyst', 'Project Manager', 'Scrum Master', 'Agile Coach',
        'Marketing Manager', 'Digital Marketing Specialist', 'Content Strategist',
        'HR Manager', 'Finance Manager', 'Operations Manager', 'Sales Manager',
        'Research Analyst', 'Lecturer', 'Associate Professor', 'PhD Student',
        'Graduate Trainee', 'Intern', 'Entrepreneur', 'Consultant',
        'Cloud Architect', 'Security Engineer', 'QA Engineer', 'Tech Lead',
    ];

    private array $mealPreferences = [
        'Halal', 'Halal', 'Halal', 'Halal', 'Halal',
        'Vegetarian', 'Vegan', 'No Preference', 'No Preference',
    ];

    private array $statuses = [
        'confirmed', 'confirmed', 'confirmed', 'confirmed', 'confirmed',
        'confirmed', 'confirmed', 'pending', 'pending', 'cancelled',
    ];

    public function run(): void
    {
        $event = Event::where('title', 'EVENT DEMO')->firstOrFail();
        $ticket = $event->tickets()->firstOrFail();
        $form = $event->registrationForms()->firstOrFail();
        $mealField = $form->fields()->where('label', 'Meal preference')->first();

        $existing = Registration::where('event_id', $event->id)->count();
        $toCreate = max(0, 150 - $existing);

        if ($toCreate === 0) {
            $this->command->info("Event Demo already has {$existing} registrations. Nothing to add.");
            return;
        }

        $this->command->info("Adding {$toCreate} registrations to Event Demo (current: {$existing})...");
        $bar = $this->command->getOutput()->createProgressBar($toCreate);
        $bar->start();

        for ($i = 0; $i < $toCreate; $i++) {
            $firstName = $this->firstNames[array_rand($this->firstNames)];
            $lastName = $this->lastNames[array_rand($this->lastNames)];
            $fullName = $firstName . ' ' . $lastName;
            $email = strtolower(str_replace(' ', '.', $firstName)) . '.' . strtolower(str_replace(' ', '', $lastName)) . ($i + 1) . '@example.com';
            $phone = '+601' . rand(0, 9) . '-' . rand(1000000, 9999999);
            $org = $this->organizations[array_rand($this->organizations)];
            $designation = $this->designations[array_rand($this->designations)];
            $status = $this->statuses[array_rand($this->statuses)];
            $meal = $this->mealPreferences[array_rand($this->mealPreferences)];

            $rawToken = 'CORE-' . $event->id . '-' . Str::upper(Str::random(36));
            $daysAgo = rand(1, 90);

            $registration = Registration::create([
                'event_id'                    => $event->id,
                'ticket_id'                   => $ticket->id,
                'registration_form_id'        => $form->id,
                'reference_number'            => 'REG-' . now()->subDays($daysAgo)->format('Ymd') . '-' . Str::upper(Str::random(8)),
                'full_name'                   => $fullName,
                'email'                       => $email,
                'phone'                       => $phone,
                'organization'                => $org,
                'designation'                 => $designation,
                'status'                      => $status,
                'payment_status'              => 'free',
                'ticket_price'                => 0,
                'discount_amount'             => 0,
                'final_amount'                => 0,
                'qr_token'                    => $rawToken,
                'qr_token_hash'               => hash('sha256', $rawToken),
                'confirmation_email_sent_at'  => in_array($status, ['confirmed', 'cancelled']) ? now()->subDays($daysAgo - 1) : null,
                'cancelled_at'                => $status === 'cancelled' ? now()->subDays(rand(1, $daysAgo - 1)) : null,
                'created_at'                  => now()->subDays($daysAgo)->addHours(rand(8, 20))->addMinutes(rand(0, 59)),
                'updated_at'                  => now()->subDays($daysAgo)->addHours(rand(8, 20))->addMinutes(rand(0, 59)),
            ]);

            $answers = [
                ['field_key' => 'full_name',     'field_label' => 'Full Name',     'field_type' => 'text',  'value' => $fullName],
                ['field_key' => 'email',          'field_label' => 'Email',         'field_type' => 'email', 'value' => $email],
                ['field_key' => 'phone_number',   'field_label' => 'Phone Number',  'field_type' => 'text',  'value' => $phone],
                ['field_key' => 'organization',   'field_label' => 'Organization',  'field_type' => 'text',  'value' => $org],
                ['field_key' => 'designation',    'field_label' => 'Designation',   'field_type' => 'text',  'value' => $designation],
            ];

            if ($mealField) {
                $answers[] = [
                    'field_key'   => $mealField->field_key,
                    'field_label' => 'Meal preference',
                    'field_type'  => 'select',
                    'value'       => $meal,
                ];
            }

            foreach ($answers as $answer) {
                RegistrationAnswer::create([
                    'registration_id' => $registration->id,
                    'field_key'       => $answer['field_key'],
                    'field_label'     => $answer['field_label'],
                    'field_type'      => $answer['field_type'],
                    'value'           => $answer['value'],
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Done. Total registrations: ' . Registration::where('event_id', $event->id)->count());
    }
}

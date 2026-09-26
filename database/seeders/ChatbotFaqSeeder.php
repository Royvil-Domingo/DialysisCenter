<?php

namespace Database\Seeders;

use App\Models\ChatbotFaq;
use Illuminate\Database\Seeder;

class ChatbotFaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            ['question' => 'What is hemodialysis?', 'answer' => 'Hemodialysis is a treatment that filters waste, extra fluid, and salt from your blood when your kidneys can no longer do this well enough on their own.', 'keywords' => 'hemodialysis,dialysis,what is dialysis'],
            ['question' => 'What should I bring to my appointment?', 'answer' => 'Please bring a valid ID, your patient reference code, and any current medications or prescriptions. Arrive at least 15 minutes early.', 'keywords' => 'bring,prepare,appointment requirements'],
            ['question' => 'How do I check my appointment schedule?', 'answer' => 'You can check your appointment using the "Check My Appointment" page on our homepage. You will need your patient reference code and birthdate.', 'keywords' => 'check appointment,schedule,reference code,my appointment'],
            ['question' => 'What are your operating hours?', 'answer' => 'Our center is open Monday to Saturday, 6:00 AM to 6:00 PM. Please contact our staff to confirm holiday schedules.', 'keywords' => 'hours,open,operating hours,what time'],
            ['question' => 'How do I reschedule or cancel my appointment?', 'answer' => 'Please contact our staff directly by phone or visit the center at least 24 hours before your scheduled appointment.', 'keywords' => 'reschedule,cancel,change appointment'],
            ['question' => 'What is a dialysis access (fistula/catheter)?', 'answer' => 'A dialysis access is the site on your body (such as an AV fistula, graft, or catheter) used to connect you to the dialysis machine during treatment.', 'keywords' => 'fistula,catheter,graft,access'],
            ['question' => 'Do you accept walk-in patients?', 'answer' => 'We recommend setting an appointment first. Please contact our staff to check same-day availability for walk-ins.', 'keywords' => 'walk-in,walk in,without appointment'],
            ['question' => 'How long does a dialysis session take?', 'answer' => 'A typical hemodialysis session lasts about 3 to 4 hours, usually done a few times a week, depending on your doctor\'s recommendation.', 'keywords' => 'how long,duration,session length,how many hours'],
            ['question' => 'What should I do if I feel unwell after treatment?', 'answer' => 'Please contact our dialysis staff or your doctor right away if you feel unwell. Do not wait for your next scheduled visit.', 'keywords' => 'feel unwell,side effects,after treatment,dizzy,not feeling well'],
            ['question' => 'How can I contact the center?', 'answer' => 'You may reach us through the contact details posted on our homepage, or visit us directly at the dialysis center.', 'keywords' => 'contact,phone,address,email,location'],
        ];

        foreach ($faqs as $faq) {
            ChatbotFaq::firstOrCreate(['question' => $faq['question']], $faq);
        }
    }
}

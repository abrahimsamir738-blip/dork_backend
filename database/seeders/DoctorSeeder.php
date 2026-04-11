<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Doctor::create([
            'name' => 'د. أحمد محمد',
            'email' => 'doctor@example.com',
            'password' => Hash::make('password123'),
            'title' => 'استشاري جراحة العظام',
            'specialty' => 'دكتوراة جراحة العظام - جامعة القاهرة',
            'bio' => 'أكثر من 15 عاماً من الخبرة في جراحات المناظير وإصابات الملاعب.',
            'photo' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&q=80&w=300&h=300'
        ]);

        Doctor::create([
            'name' => 'د. سارة أحمد',
            'email' => 'sara@example.com',
            'password' => Hash::make('password123'),
            'title' => 'استشاري طب الأطفال',
            'specialty' => 'دكتوراة طب الأطفال - جامعة عين شمس',
            'bio' => 'خبرة واسعة في علاج أمراض الأطفال والرضع.',
            'photo' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&q=80&w=300&h=300'
        ]);
    }
}

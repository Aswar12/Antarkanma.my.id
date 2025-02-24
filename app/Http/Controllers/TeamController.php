<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    public $teamMembers = [
        [
            'name' => 'Aswar Sumarlin',
            'role' => 'Founder & Lead Developer',
            'image' => 'team/fotoku.jpeg',
            'description' => 'Sebagai founder dan pengembang utama Antarkanma, saya berkomitmen untuk membangun platform pengiriman yang inovatif dan terpercaya.'
        ],
        [
            'name' => 'Husain',
            'role' => 'Marketing',
            'image' => 'team/husain.jpeg',
            'description' => 'Ahli dalam pengembangan strategi pemasaran dan pertumbuhan bisnis'
        ],
        [
            'name' => 'Akbar',
            'role' => 'Facility & Infrastructure Manager',
            'image' => 'team/akbar.jpeg',
            'description' => 'Mengoptimalkan lingkungan kerja dan infrastruktur teknis untuk mendukung inovasi dan produktivitas tim'
        ],
        [
            'name' => 'Ichal',
            'role' => 'Design',
            'image' => 'team/ichal.jpeg',
            'description' => 'Spesialis dalam UI/UX design dan pengalaman pengguna'
        ],
        [
            'name' => 'Firman',
            'role' => 'Advisor',
            'image' => 'team/firman.jpeg',
            'description' => 'Mentor dan penasehat strategis dengan pengalaman industri yang luas'
        ],
        [
            'name' => 'Hegar',
            'role' => 'Data Analyst',
            'image' => 'team/hegar.jpeg',
            'description' => 'Spesialis dalam analisis data dan pengambilan keputusan berbasis data'
        ],[
            'name' => 'Mas Fajri',
            'role' => 'Courier & Operations',
            'image' => 'team/fajri.jpeg',
            'description' => 'Bertanggung jawab dalam pengantaran pesanan dengan cepat dan memastikan kepuasan pelanggan.'
        ]

    ];

    public function uploadTeamPhotos()
    {
        foreach ($this->teamMembers as $member) {
            $localPath = public_path('images/' . basename($member['image']));
            if (file_exists($localPath)) {
                Storage::disk('s3')->putFileAs(
                    'team',
                    $localPath,
                    basename($member['image']),
                    'public'
                );
            }
        }

        return response()->json(['message' => 'Team photos uploaded successfully']);
    }

    public function getTeamMembers()
    {
        return view('sections.team', ['members' => $this->teamMembers]);
    }
}

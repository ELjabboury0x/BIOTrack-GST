<?php

namespace App\Events;

use App\Models\Complaint;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComplaintCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Complaint $complaint)
    {
    }
}

<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\FolderData;
use App\FolderPermission;
use Storage;

class DeleteMemberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $folder_id;
    public $user_id;


    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        //
        $this->folder_id = $data[0];
        $this->user_id = $data[1];
     }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = FolderData::select('file as file_name')->whereIn('folder_id', $this->folder_id)->where('user_id', $this->user_id )->get()->toArray();
        if(isset($response) && !empty($response)){
            foreach($response as $key => $value){
                if (!empty($value["file_name"]) && Storage::disk('public')->exists('files/' . $value["file_name"])) {
                    Storage::disk('public')->delete('files/'.$value["file_name"]);
                 }
                if (!empty($value["file_name"]) && Storage::disk('public')->exists('files/thumb/' . $value["file_name"])) {
                    Storage::disk('public')->delete('files/thumb/'.$value["file_name"]);
                }
            }
        }
        FolderData::whereIn('folder_id', $this->folder_id)->where('user_id', $this->user_id )->delete();
        FolderPermission::whereIn('folder_id', $this->folder_id)->where('user_id', $this->user_id )->delete();
        
         /*FolderData::whereIn('folder_id', $this->folder_id)->where('user_id', $this->user_id )->update(["status" => 3]);
        FolderPermission::whereIn('folder_id', $this->folder_id)->where('user_id', $this->user_id )->update(["status" => 0]);*/
    }
}

<x-profile :sharedData="$sharedData" docTitle="{{$sharedData['username']}}'s Followings"> <!-- avatar="$avatar" is used to get avatar from parent component -->
  @include('profile-following-only')    
</x-profile>
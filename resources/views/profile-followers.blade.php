<x-profile :sharedData="$sharedData" docTitle="{{$sharedData['username']}}'s Followers"> <!-- avatar="$avatar" is used to get avatar from parent component -->
  @include('profile-followers-only')
</x-profile>
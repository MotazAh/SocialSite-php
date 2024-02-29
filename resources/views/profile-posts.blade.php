<x-profile :sharedData="$sharedData" docTitle="{{$sharedData['username']}}'s Profile"> <!-- avatar="$avatar" is used to get avatar from parent component -->
  @include('profile-posts-only')
</x-profile>
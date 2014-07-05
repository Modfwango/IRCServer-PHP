Make configuration class
Make PONG verify subject
Implement Channel module to store channel states

Random thoughts:
* Consider removing all unnecessary events:  Anything that needs to be intercepted can be done so with a preprocessor on the commandEvent event.  What would the purpose be for channelJoinEvent for example; notifying the join command module that someone joined a channel?  Although, they could be useful for linking protocol implementations.

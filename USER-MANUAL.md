# Invitation App User Manual

## Invitation App
<p>For a federated share you need the cloud ID of the remote user. Obtaining the cloud ID from the remote user is inconvenient at best. The Invitation App facilitates controlled and user-friendly exchange of this cloud ID between your and the remote user's system. From then on you can create federated shares with each other just as easy as regular shares with local users.<br>
</p>

The exchange process consists of two basic steps:
1. the initiator/sender sends an invitation (to exchange cloud IDs)
2. the receiver accepts the invitation (to exchange cloud IDs)

<p>
The result is that, after accepting, both users can search for each other when sharing, just as they can search for local users.<br>
Federated sharing becomes as easy as local sharing.
</p>

## Instructions
Let's consider two Research Drive users from different institutes, Jimmie and Lex, who are collaborating in a national project. They want to share documents with each other through Research Drive, but since they are from different institutes they can't find each other in their Research Drive environments. The instructions below explain how the Invitation App can help with this.

---

### Where to find the Invitation App
<p>
The Invitation App can be found in the upper left-hand corner.
</p>

![Where to find the Invitation App](img/rd-1-menu-invitation-app.png "Where to find the Invitation App")<br>
Fig.1 - Where to find the Invitation App.<br>
<br>

### Sending an invitation
<p>
Jimmie has opened the Invitation App index page in his Research Drive environment.
<p>

![The Invitation index page](img/rd-1-invitation-index-page.png "Invitation index page")<br>
Fig.2 - The Invitation App index page.<br>
<br>
<p>
To create an invitation click the 'Create Invitation' button and fill in the fields.<br>
The email of the receiver does not have to be an institutional email address, but can also be a personal one.<br>
All fields are required except the message field.
</p>

![Create invitation for Lex](img/rd-1-create-invitation-for-lex.png "Create invitation for Lex")<br>
Fig.3 - Create invitation for Lex.<br>
<br>

<p>
After clicking the 'Send' button a confirmation message will popup asking you to confirm if the displayed information may be shared with the intended recipient.<br>
Click the 'Ok' button to confirm, or decline by clicking the 'x' button in the upper right-hand corner.<br>
*&nbsp;Note that the optional message will only be send with the email, but will not be exchanged with or saved on the remote user's Research Drive instance.
</p>

![Information exchange confirmation](img/rd-1-create-invitation-for-lex-confirmation.png "Information exchange confirmation")<br>
Fig.4 - Information exchange confirmation.<br>
<br>

<p>
If 'Ok' is clicked the email will be send to Lex and the invitation will appear as an open invitation to Lex.
</p>

![Open Invitation sent to Lex](img/rd-1-open-invitation-for-lex.png "Open Invitation sent to Lex")<br>
Fig.5 - Open Invitation sent to Lex.<br>
<br>

#### Revoking an invitation
<p>
At this point Jimmie may decide to revoke the invitation by clicking the 'revoke' button. This will invalidate the invitation. Even though the invitation mail has been send it will not be possible for Lex to accept the invitation, and no cloud IDs will be exchanged.
</p>

### Receiving the invitation email
<p>
Lex will now receive the invitation in his mailbox.<br>
In the email Lex can click the 'Uitnodiging accepteren' button to follow through with the invitation.
</p>
<br>

![Open Invitation sent to Lex](img/lex-invitation-mail-received-from-jimmie.png "Open Invitation sent to Lex")<br>
Fig.6 - Open Invitation sent to Lex.<br>
<br>

### Choosing the Research Drive environment of your institute to log in to
<p>
If Lex clicks the 'Uitnodiging accepteren' button he will be redirected to a page that allows him to choose the Research Drive environment of his institute.
</p>

![Choose your institute](img/wayf-invitation-for-lex.png "Choose your institute")<br>
Fig.7 - Choose your institute.<br>
<br>

### Log in to your Research Drive environment
<p>
Lex logs in to his Research Drive environment.
</p>

![Lex logs in to his Research Drive environment](img/rd-2-wayf-login-lex.png "Lex logs in to his Research Drive environment")<br>
Fig.8 - Lex logs in to his Research Drive environment.<br>
<br>

### View the received open invitation
<p>
After login Research Drive shows the Invitation App index page where the open invitation from Jimmie can be found.
</p>

![Lex sees the open invitation from Jimmie](img/rd-2-open-invitation-from-jimmie.png "Lex sees the open invitation from Jimmie")<br>
Fig.9 - Lex sees the open invitation from Jimmie.<br>
<br>

#### Open invitation notification
<p>
It is not required to handle the open invitation straight away. You can start doing other things and come back later on this page via the upper left-hand corner menu.<br>
There is also a notification for each received invitation. If you click on the notifications bell it will be displayed and you can click on it to return to the open invitation.
</p>

![Click on the notification to return to the open invitation from Jimmie](img/rd-2-open-invitation-from-jimmie-notification.png "Click on the notification to return to the open invitation from Jimmie")<br>
Fig.10 - Click on the notification to return to the open invitation from Jimmie.<br>
<br>

### Accept/decline the open invitation
<p>
If you click on the 'accept' button a confirmation popup will appear that shows what information will be exchanged with the sender of the invitation. Click the 'Ok' button to confirm, or the 'x' button in the upper right-hand corner of the popup to decline.
</p>
<p>
If you click on the 'decline' button the invitation will be removed and no user information will be exchanged with the sender. That means that there will be no enhanced support for federated sharing between sender and receiver of the invitation.
</p>

![A confirmation dialog is displayed when clicking the 'accept' button of the invitation](img/rd-2-accept-open-invitation-from-jimmie-confirmation.png "A confirmation dialog is displayed when clicking the 'accept' button of the invitation")<br>
Fig.11 - A confirmation dialog is displayed when clicking the 'accept' button of the invitation.<br>
<br>
<p>
When clicking the 'Ok' button the invitation is accepted and will be displayed as an accepted invitation.<br>
This goes for Jimmie in his Research Drive environment as well.
</p>

![Display the accepted invitation](img/rd-2-display-accepted-invitation.png "Display the accepted invitation")<br>
Fig.12 - Display the accepted invitation.<br>
<br>
#### Removing an accepted invitation
<p>
Both sender and receiver can remove an accepted invitation by clicking the 'remove' button, however, an accepted invitation exists on both sender and receiver Research Drive environments and removing it on one environment does not affect the invitation on the other. If eg. the sender removes the accepted invitation this removes the cloud ID and information of the remote user (the receiver) from the Research Drive environment of the sender. The consequence is that the sender cannot search for the receiver anymore when creating a share. A federated share for the receiver can still be created by the sender but only by filling in the cloud ID 'by hand'.<br>
Since the invitation always also exists on the receiver's environment he or she still sees the invitation as accepted and still can search for the sender when creating shares.<br>
If the receiver also decides to remove the invitation the consequences are similar but then it's the sender that cannot be found anymore by the receiver.<br>
It's important to note that removing invitations only impacts the invitation between the two users involved but not invitations they may have with other users.<br>
<br>
Please also note that <b>no existing shares are affected when removing an invitation!</b><br>
</p>

### Successful exchange of cloud IDs and enhanced federated sharing support
<p>
Lex has accepted the invitation and now Jimmie's cloud ID is known by the system, and vice versa.<br>
Both Lex and Jimmie can now experience the enhanced support for federated sharing with each other.<br>
By typing in name, email or institute name labels displaying matching users will appear. Clicking a label will create the share for that user.<br>
Note that invited remote users are clearly distinguishable from local users. See figure 13 below.
</p>

![Jimmie shares a file with invited remote user Lex](img/rd-1-share-data-with-invited-user-lex.png "Jimmie shares a file with invited remote user Lex")<br>
Fig.13 - Share a file with an invited remote user.<br>
<br>

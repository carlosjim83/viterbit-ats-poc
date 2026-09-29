
# **ATS PoC — Technical Exercise**

  

Design and build a **micro–product** that demonstrates your ability to:

-   Model a scalable, maintainable architecture (**DDD + Hexagonal + CQRS / events**).
    
-   Apply **good engineering practices** (testing and code quality).
    
-   Integrate **asynchronous processing** with messaging.
    
-   Use **AI** to enrich real candidate data.
    
-   Deliver a **simple web UI** to submit and explore applications.
    

----------

## **Product Scope**

  

Build a minimal **Application Tracking System (ATS)** with the following capabilities:

  

### **1) Apply to a Job**

-   A page with a job description where a candidate submits: full name, contact info (email/phone), position, and optional notes.
    
-   A **textarea to paste the CV as plain text** (no file upload/OCR).
    
-   On submission, store the application with appliedAt (auto) and initial status (e.g., received).
    
-   Trigger an **asynchronous AI enrichment** that:
    
    -   Produces a concise **CV summary**.
        
    -   Generates a **relevance score** based on the position applied for.
        
    

  

### **2) Browse Applications**

-   A page listing applications **newest first**.
    
-   **Real-time filtering** by status and position.
    
-   Simple **search** (e.g., by candidate name or email).
    
-   A **detail view** showing candidate data, pasted CV text, AI summary, AI score, status, and timestamps.
----------

## **Functional Requirements**

-   Submit an application containing candidate data, position, and **CV pasted as text**.
    
-   Persist the application and **initiate** the AI enrichment process asynchronously.
    
-   Update the application with **AI summary** and **AI score** when enrichment is completed.
    
-   List applications **descending by appliedAt**, with **filters** and **search**.
    
-   Provide an application **detail view**.
    

----------

## **Non-Functional Requirements**

-   Clear structure reflecting **DDD + Hexagonal + CQRS/events**.
    
-   **Unit and integration tests**
    
-   Clean, readable code and concise documentation.
    
-   Straightforward local run instructions.
    

  

_(Do not include implementation details in this document.)_

----------

## **UI Requirements**

-   **Apply Page** with inputs for candidate data and **CV textarea**.
    
-   **Applications Page** with search + filters and newest-first ordering.
    
-   **Detail Page** displaying original CV text, AI summary, AI score, status, and timestamps.
    

----------

## **AI Enrichment (asynchronous)**

-   After an application is submitted, **enqueue** an enrichment task.
    
-   The task **summarizes** the CV and **scores** relevance with respect to the selected position.
    
-   The result becomes visible in the application detail and in the list (score column).
    

----------

## **Acceptance Criteria**

-   Submitting an application creates a record with appliedAt and default status.
    
-   Asynchronous enrichment adds **summary** and **score** to that application.
    
-   Applications list is **newest first** and supports **real-time filtering** and **search**.
    
-   Detail view shows all relevant data including enrichment outputs.
    
-   Tests cover submission, filtering, and enrichment paths.
    

----------

## **Deliverables**

-   Git repository with a README.md and source code.
    
-   A make init command is desirable to start running the application.
    

----------

## **Constraints**

-   **CV input must be via textarea** (plain text).
    
-   **No file uploads or OCR**.
-   **Mock LLM requests, don't use a real API**.
    
-   Use any stack you prefer, as long as the requirements above are met.
    

----------

## **Timebox & Submission**
    
-   Share a GitHub repository link with instructions to run and test locally.
    

----------

## **Evaluation**

-   Clarity of domain boundaries and event flow.
    
-   Correctness of the user flows and asynchronous enrichment.
    
-   Code quality and test completeness.
    
-   Simplicity and usability of the UI.
    
-   Documentation sufficiency (without implementation detail).